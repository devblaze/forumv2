# ForumV2

This is a Laravel-based project built for easy deployment and development. The project uses Laravel Sail to simplify development with Docker. Follow the steps below to set up the project in your local environment.

---

## **Requirements**

Before setting up the project, ensure you have the following installed:

1. **Docker** (https://www.docker.com)
2. **Composer** (https://getcomposer.org)

---

## **Getting Started**

Follow these steps to set up and run this Laravel project on your local machine.

### **1. Clone the Repository**

```bash
git clone hhttps://github.com/devblaze/forumv2.git
cd forumv2
```

If the repository is private, ensure you have the proper access privileges.

---

### **2. Install Dependencies**

Install the required PHP dependencies using Composer:

```bash
./vendor/bin/sail composer install
```

---

### **3. Configure Environment**

Duplicate the `.env.example` file to create your `.env` file:

```bash
cp .env.example .env
```

Now, edit the `.env` file to configure your environment variables as needed. The most critical settings include:

- `DB_CONNECTION=mysql`
- `DB_HOST=mysql`
- `DB_PORT=3306`
- `DB_DATABASE=forumv2`
- `DB_USERNAME=sail`
- `DB_PASSWORD=password`

---

### **4. Start Laravel Sail**

Laravel Sail is a lightweight wrapper for Docker. To start the Sail environment, run the following:

```bash
./vendor/bin/sail up -d
```

This will start the necessary Docker containers in the background.

---

### **5. Generate Application Key**

Laravel requires an application key to be set. Generate one by running:

```bash
./vendor/bin/sail artisan key:generate
```

---

### **6. Run Database Migrations**

Run the migrations to set up the database schema:

```bash
./vendor/bin/sail artisan migrate
```

If you need initial test data, you can also seed the database:

```bash
./vendor/bin/sail artisan db:seed
```

---

### **7. Access the Project**

Once the Sail environment is running, open your browser and navigate to:

- **Local Development**: http://localhost

Make sure the application is running and accessible.

---

## **Running Tests**

This project has automated tests for various features. To run them:

```bash
./vendor/bin/sail test
```
