# Questions-and-Answers

Projekt zaliczeniowy z przedmiotu Zaawansowane Techniki Programowanie 2 (UJ, Elektroniczne Przetwarzanie Informacji)

Instrukcja uruchomienia lokalnego:

// W celach developerskich, front-end i back-end pozostaja jako osobne aplikacje. Połączona wersja aplikacji znajduje się pod adresem:
https://wierzba.wzks.uj.edu.pl/~20_kobylarz/qa-app/
// W galęzi "wierzba-setup" tego repozytorium znajdują się pliki przystosowane do serwera wierzba należącego do WZIKS UJ.
// W celu umieszczenia projektu na innym serwerze należy odpowiednio ustawić pliki:
Frontu:
- App.jsx (basename)
- api.js (  baseURL)

Backendu:
- .htaccess
- index.php


1. Backend:
- w katalogu backend: 
$ docker compose up -d (lub docker-compose, zaleznie od wersji)
$ docker compose exec php bash
$ cd app
$ composer install
$ composer init-app

- backend w załączonej konfiguracji dockera domyślnie uruchamia się na porcie 8000

2. Frontend
- w katalogu frontend:
$ npm install
$ npm run dev

- frontend w załączonej konfiguracji Vite uruchamia się na porcie 5173



~
W katalogu backendu znajduje się dokumentacja - directory docs
Oraz raport pokrycia kodu testami - directory coverage ( na poziomie ~92% pokrycie linii kodu )

~
Domyślne dane logowania wygenerowane przez DataFixtures:

| Email               | Nickname       | Hasło     |
|--------------------|----------------|-----------|
| user0@example.com   | usernick0      | user1234  |
| user1@example.com   | usernick1      | user1234  |
| user2@example.com   | usernick2      | user1234  |
| user3@example.com   | usernick3      | user1234  |
| user4@example.com   | usernick4      | user1234  |
| user5@example.com   | usernick5      | user1234  |
| user6@example.com   | usernick6      | user1234  |
| user7@example.com   | usernick7      | user1234  |
| user8@example.com   | usernick8      | user1234  |
| user9@example.com   | usernick9      | user1234  |
| admin0@example.com  | adminnick0     | admin1234 |
| admin1@example.com  | adminnick1     | admin1234 |
| admin2@example.com  | adminnick2     | admin1234 |

