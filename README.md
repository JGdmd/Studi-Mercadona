# Project Title

Studi Mercadona

# Project Description

This project is used for validates my development skills on my Studi training.

# Disclaimer

The subject was make a website about a Mercadona French store but it's totally fictitious.

# Technology

Project on Symfony, Twig and Javascript

# First Installation
For you .env file you can choice the default in the Symfony Documentation.
If you want use and test the application, you must inject the first user in sql (this application do not have a register extern page).
This is the sql code :
`INSERT INTO seller VALUES (1,MER-12345-000, "["ROLE_ADMIN"]", "$2y$13$B.NWWD5ar/qRPqNQ6mxkyO7xz2X7IpAi3XjQ2Qg/QMRUGsjzR8LiK", "yourEmail", "JASC3TP7AFYP4ZSBBPQ24FJVMPCBOLLWIUNN7VSFS444ADQHQVGA", "false")`

- 1 => First ID
- 2 => MER-12345-000 => Code user
- 3 => ["ROLE_ADMIN"] => Role of the user in app
- 4 => $2y$13$B.NWWD5ar/qRPqNQ6mxkyO7xz2X7IpAi3XjQ2Qg/QMRUGsjzR8LiK => password for Test12345 (You can change in the app)
- 5 => yourEmail => type your email
- 6 => JASC3TP7AFYP4ZSBBPQ24FJVMPCBOLLWIUNN7VSFS444ADQHQVGA => Google Authenticator Code
- 7 => false => It is for your first connexion at the application (when you flash the qrCode, this param switch to true)
