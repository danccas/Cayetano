import nltk
import pickle
from nltk.tokenize import word_tokenize


def obtener_palabras_claves(palabras):
        all_words = []
        for (words, sentiment) in palabras:
          all_words.extend(words)
        wordlist = nltk.FreqDist(all_words)
        palabras_claves = []
        for feature in wordlist:
            if wordlist[feature] < 10000:
                palabras_claves.append(feature)
        print(len(palabras_claves))
        return palabras_claves

def extract_primarys(document):
        palabras = set(document)
        resp = {}
        for p in palabras_claves:
            resp['contains(%s)' % p] = (p in palabras)
        return resp

train_positivos=[]
train_negativos=[]
test_positivos=[]
test_negativos=[]


with open('./dataset_sentimiento/train-positivo3.txt','r') as f:
    for line in f:
        train_positivos.append((line,'positivo'))
f.close()

with open('./dataset_sentimiento/train-negativo3.txt','r') as f:
    for line in f:
        train_negativos.append((line,'negativo'))
f.close()

with open('./dataset_sentimiento/test-positivo2.txt','r') as f:
    for line in f:
        test_positivos.append((line,'positivo'))
f.close()

with open('./dataset_sentimiento/test-negativo2.txt','r') as f:
    for line in f:
        test_negativos.append((line,'negativo'))
f.close()

train = []
test = []

for (words, sentiment) in train_positivos + train_negativos:
    words_filtered = [e.lower() for e in words.split() if len(e) >= 2]
    train.append((words_filtered, sentiment))

for (words, sentiment) in test_positivos + test_negativos:
    words_filtered = [e.lower() for e in words.split() if len(e) >= 2]
    test.append((words_filtered, sentiment))

print(train)
print(test)
print("Filtramos los datasets")


palabras_claves = obtener_palabras_claves(train+test)
print(palabras_claves)
print("Generamos una concatenacion")

training_set = nltk.classify.apply_features(extract_primarys, train)

test_set = nltk.classify.apply_features(extract_primarys, test)

print("Realizamos el entrenamiento")



classifier = nltk.NaiveBayesClassifier.train(training_set)
classifier.show_most_informative_features()
print("Model generated")



test = 'si'
t_features = {word: (word in word_tokenize(test.lower())) for word in palabras_claves}
print(test," : ", classifier.classify(t_features))



f = open('classifier_sentimiento.pickle', 'wb')
pickle.dump(classifier, f)
f.close()

# f = open('twitter_classifier.pickle', 'rb')
# classifier = pickle.load(f)
# f.close()
#
# print("Model loaded")

accuracy = nltk.classify.accuracy(classifier,test_set)


print(accuracy)
