import nltk
import pickle
def obtener_palabras_claves(palabras):
        all_words = []
        for (words, sentiment) in palabras:
          all_words.extend(words)
        wordlist = nltk.FreqDist(all_words)
        palabras_claves = []
        for feature in wordlist:
            if wordlist[feature] > 10000:
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


with open('./dataset_sentimiento/train-positivo.txt','r') as f:
    for line in f:
        train_positivos.append((line,'positivo'))
f.close()

with open('./dataset_sentimiento/train-negativo.txt','r') as f:
    for line in f:
        train_negativos.append((line,'negativo'))
f.close()

with open('./dataset_sentimiento/test-positivo.txt','r') as f:
    for line in f:
        test_positivos.append((line,'positivo'))
f.close()

with open('./dataset_sentimiento/test-negativo.txt','r') as f:
    for line in f:
        test_negativos.append((line,'negativo'))
f.close()

train = []
test = []

for (words, sentiment) in train_positivos + train_negativos:
    words_filtered = [e.lower() for e in words.split() if len(e) >= 3]
    train.append((words_filtered, sentiment))

for (words, sentiment) in test_positivos + test_negativos:
    words_filtered = [e.lower() for e in words.split() if len(e) >= 3]
    test.append((words_filtered, sentiment))

print("Filtramos los datasets")


palabras_claves = obtener_palabras_claves(train+test)
print("Generamos una concatenacion")

training_set = nltk.classify.apply_features(extract_primarys, train)

test_set = nltk.classify.apply_features(extract_primarys, test)

print("Realizamos el entrenamiento")
print(training_set)
classifier = nltk.NaiveBayesClassifier.train(training_set)
print("Model generated")

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
