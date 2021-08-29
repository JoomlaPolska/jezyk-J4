# Współpraca z Joomla!Polska/jezyk-J4 
(język polski dla Joomla! 4.0 i nowszych)

:+1::tada: Po pierwsze, dzięki za poświęcanie czasu, aby wnieść wkład! :tada::+1:

Nasz  [Kodeks postępowania](../CODE_OF_CONDUCT.md). Proszę przeczytać uważnie

Rozpoczęcie pracy z [git i github](https://guides.github.com/activities/hello-world/). Jeśli nie masz git na swoim komputerze, [zainstaluj go]( https://help.github.com/articles/set-up-git/).
#### *Jeśli nie czujesz się komfortowo z wierszem poleceń, [tutaj są samouczki, jak korzystać z narzedzi GUI.]( #tutorials-using-other-tools )*

## Praca z tym repozytorium

### Sforkuj to repozytorium

Sforkuj to repozytorium, wybierając na przycisk "Fork" na górze tej strony. 
Spowoduje to utworzenie kopii tego repozytorium na Twoim koncie.

Zwróć uwagę, na to, którą gałąź repozytorium kopiujesz. Najbardziej aktualne pliki językowe znajdują się zawsze w galęzi oznaczonej numerem najbliższej planowanej wersji Joomla, np. pakiet-4.0.3.

### Sklonuj repozytorium

Teraz sklonuj rozwidlone repozytorium na swój komputer. Przejdź na swoje konto GitHub, otwórz rozwidlone repozytorium, kliknij przycisk klonowania, a następnie kliknij ikonę *copy to clipboard* (kopiuj do schowka).

Otwórz terminal i uruchom następujące polecenie git:

```
git clone "url, który właśnie skopiowałeś"
```
gdzie "url, który właśnie skopiowałeś" (bez cudzysłowu) jest adresem url do tego repozytorium (twojego forka tego projektu). Zobacz poprzednie kroki, aby uzyskać adres url.

Na przykład:


```
git clone https://github.com/to-twoje-konto/joomla.git
```
gdzie `to-twoje-konto` jest Twoją nazwą użytkownika na GitHubie. Tutaj skopiujesz zawartość repozytorium joomla z GitHuba na swój komputer.

### Załóż problem lub podejmij się rowiązania (issue)



Przejdź do [Issues](https://github.com/JoomlaPolska/jezyk-J4/issues) i utwórz nowy problem lub wykorzystaj otwarty problem do napisania PR.  
Zazwyczaj nie ma PR bez wcześniejszego zgłoszenia problemu. Mamy *zasadę najpierw _issue_*.


### Utwórz rozgałęzienie

Przejdź do katalogu repozytorium na swoim komputerze (jeśli jeszcze tam nie jesteś):

```
cd joomla
```
Teraz utwórz gałąź, używając polecenia `git checkout`:
```
git checkout -b <tu-nazwa-twojej-nowej-gałęzi>
```

Na przykład:
```
git checkout -b <numer-problemu>
```
(Nazwa gałęzi nie musi mieć numeru problemu w nazwie, ale jest to łatwy sposób, aby odnieść się do konkretnego problemu).

### Dokonaj niezbędnych zmian i zatwierdź te zmiany

Teraz otwórz wszystkie pliki w edytorze tekstowym lub w IDE np. PhpStorm i wprowadź zmiany. Następnie zapisz plik.

Jeśli wejdziesz do katalogu projektu i wykonasz polecenie `git status`, zobaczysz, że są zmiany.


Dodaj te zmiany do gałęzi, którą właśnie utworzyłeś używając polecenia `git add`:

```
git add .
```

Teraz zatwierdź te zmiany, używając komendy `git commit`:
```
git commit -m "np. łatka #<numer-problemu>"
```
zamień `<numer-problemu>` numerem problemu.

### Prześlij zmiany na GitHub

Pchnij swoje zmiany, używając polecenia 


`git push`:
```
git push origin <tu-wpisz-swoja-nazwe>
```
zamień `<tu-wpisz-swoja-nazwe>` na nazwę gałęzi, którą utworzyłeś wcześniej.

### Zgłoś swoje zmiany do recenzji

Jeśli przejdziesz do swojego repozytorium na GitHubie, zobaczysz przycisk  `Compare & pull request` (_Porównaj i wyślij prośbę o rozpatrzenie_). Kliknij na ten przycisk.

Teraz wyślij pull request (żądanie scalenia).


Zawsze będziemy się starać reagować na zgłoszenia lub scalać PR tak szybko, jak to możliwe. Otrzymasz wiadomość e-mail z powiadomieniem, gdy zmiany zostaną scalone.


### Co dalej?

Gratulacje!  Właśnie ukończyłeś standardowy proes pracy _fork -> clone -> edit -> PR_, z którym często będziesz się spotykał jako współtwórca!

