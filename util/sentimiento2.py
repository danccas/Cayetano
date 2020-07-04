import pandas as pd
import nltk
import random
import pickle
from nltk.tokenize import word_tokenize

#nltk.download('punkt')

poss = pd.read_csv('dataset_sentimiento/train-positivo3.txt')
negs = pd.read_csv('dataset_sentimiento/train-negativo3.txt')
poss.columns = ["text"]
negs.columns = ["text"]

data=([(pos['text'], 'positivo') for index, pos in poss.iterrows()]+
    [(neg['text'], 'negativo') for index, neg in negs.iterrows()])

tokens=set(word.lower() for words in data for word in word_tokenize(words[0]))
train=[({word:(word in word_tokenize(x[0])) \
         for word in tokens}, x[1]) for x in data]

print(tokens)
print(train[0])

random.shuffle(train)
train_x=train[0:10]
test_x=train[10:15]

model = nltk.NaiveBayesClassifier.train(train_x)
acc=nltk.classify.accuracy(model, test_x)
print("Accuracy:", acc)



f = open('classifier_sentimiento.pickle', 'wb')
pickle.dump(model, f)
f.close()

model.show_most_informative_features()


tests=['si', 
    'no',
    'enfermo',
    'estoy mejorando',
    'todos los días',
    'una cosas,',
    'ya no']

for test in tests:
 t_features = {word: (word in word_tokenize(test.lower())) for word in tokens}
 print(test," : ", model.classify(t_features)) 
