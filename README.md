---
## Web-Based HTML File Manager & Editor

This project provides a straightforward web-based tool for managing HTML files and images on your server. It allows you to create, edit, delete, and rename HTML files, and upload images to a specified directory. The editor used in this script is the open-source version of TinyMCE.

This project was initially developed to explore Tailwind CSS and may not be suitable for production environments. We highly recommend it for testing and learning purposes.

### Features

 File Management: Create, edit, delete, and rename HTML files.
 Image Uploads: Upload images to a designated directory.
 Intuitive Interface: Files are displayed as cards for easy navigation.
 TinyMCE Editor: Integrated rich-text editor for HTML content.

---

### Installation Instructions

To get started, follow these steps:

1.  Configure `config.php`:

     Navigate to the `private` folder.
     Open `config.php` in a text editor.
     Set your desired `USERNAME` and `PASSWORD`: Replace `'your_secure_username'` with your chosen username and `'your_strong_password'` with your desired password. The password will be automatically hashed for security.

        ```php
        <?php
       
        define('USERNAME', 'your_secure_username'); 
        define('PASSWORD_HASH', password_hash('your_strong_password', PASSWORD_DEFAULT)); 
        define('FILES_DIR', __DIR__ . '/../public_html/Files'); 
        define('IMAGES_DIR', __DIR__ . '/../public_html/images');
        ?>
        ```

     Adjust file paths: The `FILES_DIR` and `IMAGES_DIR` use `__DIR__` to define paths relative to the `config.php` file. You might need to change `public_html` in these lines to reflect your server's webroot directory (e.g., `htdocs`, `www`, `html`, etc.).

        For example, if your webroot is `htdocs`, you'd change:
        `define('FILES_DIR', __DIR__ . '/../public_html/Files');`
        to
        `define('FILES_DIR', __DIR__ . '/../htdocs/Files');`

        Here's a conceptual diagram of the directory structure:

        ```
        your_server_root/
        ├── private/
        │   └── config.php
        │   
        └── public_html/ (or your webroot, e.g., htdocs, www, html)
            ├── index.php
            ├── Files/ (where your HTML files will be stored)
            ├── images/ (where your images will be stored)
            └── ... (other public files and folders)
        ```

2.  Upload Files:

     Upload the entire contents of the `public_html` folder to your web server's public webroot directory (e.g., `public_html`, `www`, `htdocs`).
     Upload the `private` folder and its contents outside your public webroot. This is crucial for security, as it prevents direct web access to your configuration files.
     Navigate to edit.php (i.e. localhost/edit.php) and login to access the file manager.
    
    Here's an illustration of the recommended file placement:

    ```
    +----------------------+
    |    Server Root       |
    |                      |
    | +------------------+ |
    | |     private/     | | <--- OUTSIDE webroot (e.g., /home/user/private/)
    | |   config.php     | |
    | |      ...         | |
    | +------------------+ |
    |                      |
    | +------------------+ |
    | |  public_html/    | | <--- YOUR WEBROOT (e.g., /home/user/public_html/)
    | |    index.php     | |
    | |  Files/          | |
    | |    images/       | |
    | |      ...         | |
    | +------------------+ |
    +----------------------+
    ```

---

### Security Considerations

Important: This project is intended for testing and learning purposes only. Do not install it on a live production server without implementing robust security measures. While some basic security has been applied, you'll need to:

 Implement a `.htaccess` file with proper restrictions in your public webroot to further secure your application. This might include restricting access to certain directories or files, setting up rewrite rules, and preventing directory listings.
 Regularly update TinyMCE and any other third-party libraries used in the project to patch potential vulnerabilities.

Enjoy experimenting with this file manager!

---

### Issues On Windows Test Server

Symlinks used in some Windows test servers will return false for `realpath()` PHP function. This results in an "outside designated directory" error while using the `edit.php` file. I have included a `test-edit.php` file that does not use `realpath()`, it can be used to replace the code in the `edit.php` file if needed for local testing.

---

![index.php full screen](https://ik.imagekit.io/umtqd7igd/files/card1.jpeg "index.php full screen")
![index.php half screen](https://ik.imagekit.io/umtqd7igd/files/card2.jpeg "index.php half screen")
![login page](https://ik.imagekit.io/umtqd7igd/files/card3.jpeg "login page")
![edit.php](https://ik.imagekit.io/umtqd7igd/files/card4.jpeg "edit.php")
![edit.php editing a file](https://ik.imagekit.io/umtqd7igd/files/card5.jpeg "edit.php editing a file")
