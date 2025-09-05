# Questions-and-Answers
# Questions-and-Answers

Projekt zaliczeniowy z przedmiotu **Zaawansowane Techniki Programowania 2** (UJ, Elektroniczne Przetwarzanie Informacji)

---

## Uruchomienie na serwerze Wierzba

> W celach developerskich front-end i back-end pozostają jako osobne aplikacje.  
> Połączona wersja aplikacji dostępna jest pod adresem:  
> [https://wierzba.wzks.uj.edu.pl/~20_kobylarz/qa-app/](https://wierzba.wzks.uj.edu.pl/~20_kobylarz/qa-app/)

W gałęzi `wierzba-setup` repozytorium znajdują się pliki przystosowane do serwera Wierzba (WZIKS UJ).  

### Pliki do ustawienia przy przenoszeniu projektu na inny serwer:

**Front-end:**
- `App.jsx` (basename)
- `api.js` (baseURL)

**Back-end:**
- `.htaccess`
- `index.php`

> Następnie należy wykonać:
```bash
$ npm run build
```

i przerzucić zawartość folderu `dist` do katalogu `public` w backendzie.

---

## Uruchomienie lokalne

### 1. Backend

W katalogu `backend`:

```bash
$ docker compose up -d   # lub docker-compose, zależnie od wersji
$ docker compose exec php bash
$ cd app
$ composer install
$ composer init-app
```

* Backend w załączonej konfiguracji Dockera domyślnie uruchamia się na **porcie 8000**.

### 2. Frontend

W katalogu `frontend`:

```bash
$ npm install
$ npm run dev
```

* Frontend w konfiguracji Vite uruchamia się na **porcie 5173**.

---

## Dokumentacja i raporty

* W katalogu `backend/docs` znajduje się dokumentacja projektu.
* W katalogu `backend/coverage` znajduje się raport pokrycia kodu testami (\~92% linii kodu).

---

## Domyślne dane logowania

Poniższe konta są generowane przez `UserFixtures` w celu testów:

| Email                                           | Nickname   | Hasło     |
| ----------------------------------------------- | ---------- | --------- |
| [user0@example.com](mailto:user0@example.com)   | usernick0  | user1234  |
| [user1@example.com](mailto:user1@example.com)   | usernick1  | user1234  |
| [user2@example.com](mailto:user2@example.com)   | usernick2  | user1234  |
| [user3@example.com](mailto:user3@example.com)   | usernick3  | user1234  |
| [user4@example.com](mailto:user4@example.com)   | usernick4  | user1234  |
| [user5@example.com](mailto:user5@example.com)   | usernick5  | user1234  |
| [user6@example.com](mailto:user6@example.com)   | usernick6  | user1234  |
| [user7@example.com](mailto:user7@example.com)   | usernick7  | user1234  |
| [user8@example.com](mailto:user8@example.com)   | usernick8  | user1234  |
| [user9@example.com](mailto:user9@example.com)   | usernick9  | user1234  |
| [admin0@example.com](mailto:admin0@example.com) | adminnick0 | admin1234 |
| [admin1@example.com](mailto:admin1@example.com) | adminnick1 | admin1234 |
| [admin2@example.com](mailto:admin2@example.com) | adminnick2 | admin1234 |


