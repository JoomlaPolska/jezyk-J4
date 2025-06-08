# Przygotowanie języka

## Wymagania
- System Windows/Linux
- PHP 8.1+ (dostępne globalnie, sprawdź wpisując w konsoli `php --version`)
- Composer (dostępny globalnie, sprawdź wpisując w konsoli `composer --version`)

## Proces budowania
- Przejdź w terminalu do katalogu głównym repozytorium
- Wpisz w `build.properties` w stałej `build.version` wersję tłumaczenia (np. `5.4.1.1`)
- Uruchom proces budowania paczki wykonując komendę `composer build`
- Gotowa paczka instalacyjna znajdzie się w `.build/pl-PL_joomla_lang_full_v5.4.1v1.zip`

## Publikacja tłumaczenia
- Wrzuć paczkę instalacyjną na https://downloads.joomla.org/language-packs/translations-joomla5
- Opublikuj zmiany w https://github.com/JoomlaPolska/jezyk-J4
  - Utwórz tag dla nowej wersji 
  - Utwórz release z tego tagu
  - Wrzuć do release utworzony plik instalacyjny tłumaczenia np. `pl-PL_joomla_lang_full_v5.4.1.1.zip`

## Publikacja zmian w Crowdin i instalatorze Joomla!
W [joomla/core-translations](https://github.com/joomla/core-translations) powinna być publikowana zawsze najnowsza wersja dla głównej wersji Joomla! (np. 5.3.1 lub 6.0.0).
  - Skopiuj zmiany z katalogu `./build/crowdin` do repozytorium [JoomlaPolska/core-translations](https://github.com/JoomlaPolska/core-translations)
  - Utwórz PR ze zmian w [JoomlaPolska/core-translations](https://github.com/JoomlaPolska/core-translations) do  [joomla/core-translations](https://github.com/joomla/core-translations)