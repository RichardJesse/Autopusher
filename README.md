# Autopusher 🚀

> **It's time to concentrate on coding—Autopusher handles the committing for you!**

Autopusher is a tool designed to automate the process of committing and pushing your code to Git. By taking care of these repetitive tasks, Autopusher allows you to focus on what matters most: writing great code.

## Features
- **Automatic Commits**: No more manual commits. Autopusher commits your changes with a default or custom message.
- **Push to Remote**: Automatically pushes your changes to the specified branch.
- **Error Handling**: Handles common Git errors and provides user-friendly feedback.

## Usage

To start using Autopusher, simply download the `autopush.php` file into your project directory.

Once downloaded, run the following command in your terminal:

```bash
php autopush.php
```

## Customizations

# Directory

You can tell the autopusher where it can should look for changes by default it looks for changes
in the whole directory that it is in(Working Directory)

to change it just go to the  `autopush.php` file and change it to what ever you want

```php
/**
     * Directory for which the autopush should 
     * look for changes in the files in it
     */
    public $directory = __DIR__;
```

# Interval

By default the autopusher check for the changes in 5 second interval you can change this in the autopush.php 
file and the interval will be set

```php
/**
     * Set the interval for which autopush 
     * should evaluating the code for pushing Duration in seconds
     * 
     */
    public $interval = 5;
```

## Finally 

Since the autopusher is just a tool you do not want it to be tracked by git but you want it to be present in your 
working directory you can do use this command to ensure that 

```bash
  git rm --cached autopush.php
```

# OR
                     
You could just create a .gitignore file and add 

autopush.php




