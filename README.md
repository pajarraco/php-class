# PHP-Class

A small collection of reusable PHP utility classes:

- **`CodeGen`** — random alphanumeric code generator
- **`SendMail`** — two-way HTML email sender (e.g. for contact forms)
- **`ImgUploader`** — image upload handler that resizes an image and generates a thumbnail

Requires **PHP 8.1+** (and the `gd` extension for `ImgUploader`). Classes are namespaced under `PhpClass\` and autoloaded via [Composer](https://getcomposer.org/) (PSR-4).

## Installation

```bash
composer install
```

This project isn't published on Packagist; if you want to pull it into another project, require it as a local path or VCS repository, or just copy the `src/` directory.

## Usage

In each example, `require 'vendor/autoload.php';` must run first so the classes can be autoloaded.

### CodeGen

Generates a random string of a given length, made up of digits, uppercase and lowercase letters (using `random_int()`, so it's safe to use for one-time codes or tokens).

```php
use PhpClass\Generator\CodeGen;

$codeGen = new CodeGen();
$code = $codeGen->generate(8); // e.g. "aB3k9Zq1"
```

### SendMail

Sends a message to one address (`$sender`) and a second message (`$messageBack`) to another address (`$receiver`), each with the other party set as the `From` header. This is the classic "contact form" pattern: the form owner gets the inquiry, and the person who submitted the form gets an automatic copy/confirmation.

```php
use PhpClass\Mail\SendMail;

$mailer = new SendMail(
    sender: 'owner@example.com',       // receives $message
    receiver: 'visitor@example.com',   // receives $messageBack
    subject: 'New contact form submission',
    message: '<p>Someone submitted the contact form.</p>',
    messageBack: '<p>Thanks for reaching out, we will reply soon.</p>',
);

$sent = $mailer->send(); // true if both emails were accepted for delivery
```

Header values are sanitized internally to strip `\r`/`\n`, which prevents header-injection attacks if any of the values come from user input.

### ImgUploader

Validates and resizes an uploaded image, optionally saving the untouched original and a proportionally-scaled thumbnail alongside the resized image. The uploaded file's actual content is inspected with `getimagesize()` — the client-supplied MIME type is never trusted.

```php
use PhpClass\Upload\ImgUploader;
use RuntimeException;

$uploader = new ImgUploader();
$uploader->pathOriginal = null;              // don't keep the original, or set a writable path
$uploader->pathImages = '/var/www/uploads';  // must be writable
$uploader->pathThumbs = '/var/www/thumbs';   // must be writable, or null to skip thumbnails
$uploader->imageWidth = 800;
$uploader->thumbWidth = 150;
$uploader->allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

try {
    $html = $uploader->upload($_FILES['img_file']); // HTML snippet linking to image + thumb
    $filename = $uploader->filename;                 // e.g. "3f9a1c2b8d4e5f6a.jpg"
} catch (RuntimeException $e) {
    // no file uploaded, disallowed extension, or the file isn't a valid image
}
```

`pathImages`, `pathThumbs` and `pathOriginal` (when set) must already exist and be writable — the constructor throws a `RuntimeException` otherwise.

See [`examples/upload_image.php`](examples/upload_image.php) for a complete upload form.

## Project layout

```
src/
  Generator/CodeGen.php
  Mail/SendMail.php
  Upload/ImgUploader.php
examples/
  upload_image.php
composer.json
```
