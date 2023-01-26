# Sales Tracker

A lightweight, flexible, and scalable sales tracker solution for managing real-time sales.

## Features
- Inventory
- Departments mgt.
- Orders
- Staff Mgt.
- Customer Mgt
- Category Mgt.
- Realtime Analytics

## Installation

### 1.0 Requirements
Salestracker is a PHP/Laravel application. In addition, it's built on [Filament PHP]('https://filamentphp.com').

Based on **PHP ^8.0.2** and **Laravel** ^9

### 2.0 Installation Steps
#### 2.0.1 Clone the repo 
```bash 
git clone git@github.com:emmadonjo/salestracker.git salestracker
```

#### 2.0.2 Install Dependencies
```bash
composer i
```
#### 2.0.3 Configure Env
Copy the contents of `.env.example` to `.env`:
```bash
cp .env.example.com .env
``` 
Update the details in the `.env` file to meet your development needs. Most importantly, the database values.

#### 2.0.3 Create a user
To create a user, enter the command:
```bash
php artisan make:filament-user
```
and follow the prompts

#### 2.0.3 Start the server
Start the server with:
```bash
php artisan serve
```

Now visit http://your-website-address/admin/login to login.

## Adding Features
As this is not normal Laravel application, you'll need to understand the technology used to add new features.

- [Laravel Documentation]('https://laravel.com/docs')
- [Filament PHP]('https://filamentphp.com')


## Todo
- Landing Page
- Receipt printing
- Permission Control

Contributions are generally welcomed.


