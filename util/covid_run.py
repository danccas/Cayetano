import numpy as np
import tensorflow as tf
import os
import pickle
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '2'


def label_encode(label):
    val = []
    if label == "Iris-setosa":
        val = [1, 0, 0]
    elif label == "Iris-versicolor":
        val = [0, 1, 0]
    elif label == "Iris-virginica":
        val = [0, 0, 1]
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
n_output = 3

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


tf.reset_default_graph()

oSaver = tf.compat.v1.train.Saver() #tf.train.Saver()

with tf.Session() as sess:

    oSaver.restore(sesss, 'tensor_memory.pickle')
    print ("result: ", sess.run(pred, feed_dict = {X: [[0,0,0,0,0,0,0]] }))
