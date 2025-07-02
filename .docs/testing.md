# Testowanie tłumaczenia

## Proces testowania integralności tłumaczenia
### Wymagania
- PHP 8.1+
- Composer

### Procest testowania
- Przejdź w terminalu do katalogu repozytorium
- Zainstaluj wymagane paczki `composer install`
- Uruchom procedurę testowania `composer test:translation`

## Proces testowania instalacji
### Wymagania
- Docker

### Procest testowania
- Przejdź w terminalu do katalogu repozytorium
- Uruchom testową instalację Joomla!, uruchamiając Docker-a: `docker compose up`
- Przejdź do [panelu administracyjnego](http://localhost/administrator)
- Zainstaluj paczkę tłumaczenia

#### Dane logowania do panelu administracyjnego
- Użytkownik: `test`
- Hasło: `testtesttesttest`