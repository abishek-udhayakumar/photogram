# How to Run "Photogram"

## Prerequisites
- PHP 7.4 or higher
- MySQL / MariaDB

## Installation Steps

1.  **Database Setup**
    - Create a database named `photogram_db`.
    - Import the `database.sql` file into this database.
    - *Note*: The code assumes User `root` and Password `` (empty) for localhost. Modify `includes/db.php` if your config is different.

2.  **Start PHP Server**
    Open a terminal in the project root directory and run:
    ```bash
    php -S localhost:8000
    ```

3.  **Access the App**
    - Open Browser: `http://localhost:8000`
    - **User App**: [http://localhost:8000](http://localhost:8000)
    - **Admin Panel**: [http://localhost:8000/admin](http://localhost:8000/admin)
        - Default Admin Login:
        - Username: `admin`
        - Password: `admin123`

## Features
- **Register/Login**: Secure auth with password hashing.
- **Feed**: See posts from people you follow.
- **Interactions**: Like, Comment, Follow/Unfollow.
- **Profile**: View your own or others' grid.
- **Search**: Find users.
- **Dark Mode**: Toggle in user dropdown.
