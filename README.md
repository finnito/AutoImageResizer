# 🌅 Auto Image Resizer

This small and simple Lumen application takes images (via a URL) and resizes them as per your constraint and returns the resized image. This is intended to help with developers wanting to responsively serve differently sized images on the frontend.

✅ No more manually resizing images

✅ No more 4000px wide images on mobile devices

✅ No more deciding ahead of time the image size needed

## 🏁 Getting Started

1. Clone this repository into your server, e.g.:

```bash
git clone git@gitlab.com:Finnito/AutoImageResizer.git /srv/imgs.lesueur.nz
```

2. Jump into that directory and install using `composer`

```bash
cd /srv/imgs.lesueur.nz
composer install
```

3. Edit your `.env` file. I have provided a `.env-example` file for you to edit.

```bash
mv .env-example .env
nano .env
```

The Laravel command `php artisan key:generate` is not available, so I used a website to generate a 256-bit key: https://www.allkeysgenerator.com/Random/Security-Encryption-Key-Generator.aspx 

```
APP_NAME=                           # Give it a name
APP_ENV=                            # development/staging/production
APP_KEY=                            # 256-bit key
APP_DEBUG=TRUE                      # Make this false in production
APP_URL=                            # The domain you are installing this application behind
APP_TIMEZONE="Pacific/Auckland"     # Your timezone (https://www.w3schools.com/php/php_ref_timezones.asp)
URL_CONSTRAINT=finn.lesueur.nz,upload.wikimedia.org #C an comma separate multiple hosts
```

4. Setup an Apache VHost

```bash
cd /etc/apache2/sites-available/
sudo nano imgs.lesueur.nz.conf
```

```
<VirtualHost *:80>
    ServerName imgs.lesueur.nz
    ServerAlias www.imgs.lesueur.nz
    DocumentRoot /srv/imgs.lesueur.nz/public/
</VirtualHost>
```

```bash
sudo a2ensite imgs.lesueur.nz.conf
sudo systemctl reload apache2
```

5. Add the A Records to your DNS!

6. Enable HTTPS!

```bash
sudo certbot --apache
```

7. Use it like this!

```
http://imgs.lesueur.nz/512?u=https://finn.lesueur.nz/posts/brass-monkey-bivvy/IMG_7936.jpg
```



