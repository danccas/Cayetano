import sys
import pandas as pd
import nltk
import random
import pickle
from nltk.tokenize import word_tokenize

#nltk.download('punkt')

poss = pd.read_csv('/var/www/html/cayetano.anccas.org/util/dataset_sentimiento/train-positivo2.txt')
negs = pd.read_csv('/var/www/html/cayetano.anccas.org/util/dataset_sentimiento/train-negativo2.txt')
poss.columns = ["text"]
negs.columns = ["text"]

data=([(pos['text'], 'positivo') for index, pos in poss.iterrows()]+
    [(neg['text'], 'negativo') for index, neg in negs.iterrows()])

tokens=set(word.lower() for words in data for word in word_tokenize(words[0]))

f = open('/var/www/html/cayetano.anccas.org/util/classifier_sentimiento.pickle', 'rb')
classifier = pickle.load(f)
f.close()


test = sys.argv[1]
t_features = {word: (word in word_tokenize(test.lower())) for word in tokens}
resp = classifier.classify(t_features)
print(resp)
