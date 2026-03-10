# Questions-and-Answers

Course project for **Advanced Programming Techniques 2** (UJ, Electronic Information Processing)

A full-stack Q&A web application with a **Symfony backend API** and **React frontend**. Features include CRUD for questions, categories, tags, users, JWT authentication, dark/light mode, i18n translations, and comprehensive testing (~92% coverage).

---

## 🌐 Live Demo

The combined application is deployed on the **Wierzba server (WZIKS UJ)**:
[https://wierzba.wzks.uj.edu.pl/~20_kobylarz/qa-app/](https://wierzba.wzks.uj.edu.pl/~20_kobylarz/qa-app/)

The `wierzba-setup` branch contains files adapted for deployment on the Wierzba server.

---

## ⚙️ Deployment Notes

### Frontend

Adjust these files before deploying to another server:

* `App.jsx` → `basename`
* `api.js` → `baseURL`

After adjustments:

```bash
npm run build
```

Then copy the contents of the `dist` folder into the backend's `public` directory.

### Backend

Adjust as needed:

* `.htaccess`
* `index.php`

---

## 🖥️ Local Setup

### 1. Backend

Navigate to the `backend` directory and run:

```bash
docker compose up -d   # or docker-compose, depending on your version
docker compose exec php bash
cd app
composer install
composer init-app
```

The backend runs by default on **port 8000** in the provided Docker configuration.

### 2. Frontend

Navigate to the `frontend` directory and run:

```bash
npm install
npm run dev
```

The frontend runs on **port 5173** using Vite’s default configuration.

---

## 📚 Documentation & Testing

* **Project documentation:** `backend/docs`
* **Test coverage (~92% of code lines):** `backend/coverage`

The project uses **PHPUnit** for unit and integration tests.

---

## 🔑 Default Login Accounts (UserFixtures)

| Email                                           | Nickname   | Password  |
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
