import numpy as np
import tensorflow as tf
import os
import pickle
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '2'


def label_encode(label):
    val = []
    if label == "si":
        val = [1, 0]
    elif label == "no":
        val = [0, 1]
    return val


def data_encode(file):
    X = []
    Y = []
    train_file = open(file, 'r')
    for line in train_file.read().strip().split('\n'):
        line = line.split(',')
        X.append([line[0], line[1], line[2], line[3], line[4], line[5], line[6]])
        Y.append(label_encode(line[7]))
    return X, Y


file = "data-train.txt"
train_X, train_Y = data_encode(file)


learning_rate = 0.01
training_epochs = 5000
display_steps = 100

n_input = 7
n_hidden = 10
n_output = 2

X = tf.placeholder("float", [None, n_input])
Y = tf.placeholder("float", [None, n_output])

weights = {
    "hidden": tf.Variable(tf.random_normal([n_input, n_hidden])),
    "output": tf.Variable(tf.random_normal([n_hidden, n_output])),
}

bias = {
    "hidden": tf.Variable(tf.random_normal([n_hidden])),
    "output": tf.Variable(tf.random_normal([n_output])),
}


def model(X, weights, bias):
    layer1 = tf.add(tf.matmul(X, weights["hidden"]), bias["hidden"])
    layer1 = tf.nn.relu(layer1)

    output_layer = tf.matmul(layer1, weights["output"]) + bias["output"]
    return output_layer


train_X, train_Y = data_encode("data-train.txt")
test_X, test_Y = data_encode("data-test.txt")

pred = model(X, weights, bias)

cost = tf.reduce_mean(
    tf.nn.softmax_cross_entropy_with_logits(logits=pred, labels=Y))
optimizador = tf.train.AdamOptimizer(learning_rate).minimize(cost)

init = tf.global_variables_initializer()

with tf.Session() as sess:
    sess.run(init)

    for epochs in range(training_epochs):
        _, c = sess.run([optimizador, cost], feed_dict={
                        X: train_X, Y: train_Y})
        if(epochs + 1) % display_steps == 0:
            print("Epoch:", epochs+1, "Cost:", c)

    print("Optimization Finished")

    test_result = sess.run(pred, feed_dict={X: train_X})
    correct_prediction = tf.equal(
        tf.argmax(test_result, 1), tf.argmax(train_Y, 1))
    accuracy = tf.reduce_mean(tf.cast(correct_prediction, "float"))

    print("accuracy:", accuracy.eval({X: test_X, Y: test_Y}))


    oSaver = tf.train.Saver()
    oSaver.save(sess, 'tensor_memory.pickle')

    print ("result: ", sess.run(pred, feed_dict = {X: [[0,0,0,0,0,0,0]] }))
