# Testowanie tłumaczenia

## Proces testowania integralności tłumaczenia
### Wymagania
- PHP 8.1+
- Composer

### Proces testowania
- Przejdź w terminalu do katalogu repozytorium
- Zainstaluj wymagane paczki `composer install`
- Uruchom procedurę testowania `composer test:translation 5.3.3` (testowanie z Joomla! 5.3.3)
  - Testowanie tłumaczenia z uwzględnieniem fraz, które mają tę samą wartość w EN i PL `composer test:translation -- 5.3.3 --find-untranslated`
  - Testowanie tłumaczenia z ignorowaniem fraz, które zostały usunięte w oryginalnym pliku `composer test:translation -- 5.3.3 --ignore-obsolete`

## Proces testowania instalacji
### Wymagania
- Docker

### Proces testowania
- Przejdź w terminalu do katalogu repozytorium
- Uruchom testową instalację Joomla!, uruchamiając Docker-a: `docker compose up`
- Przejdź do [panelu administracyjnego](http://localhost/administrator)
- Zainstaluj paczkę tłumaczenia

#### Dane logowania do panelu administracyjnego
- Użytkownik: `test`
- Hasło: `testtesttesttest`