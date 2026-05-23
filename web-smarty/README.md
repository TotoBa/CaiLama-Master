# Private Website App

Dieser Ordner enthält die CaiLama-eigenen Templates, Content-Daten und den
Bootstrap für die öffentliche Website. Er wird auf dem Webspace als privater
Sibling von `public/` nach `smarty/` deployt.

Die Smarty-Library gehört nicht ins Git-Repository. Benötigt wird:

```text
smarty/smarty ^5.0
```

Für ein lokales Deployment wird die Abhängigkeit in diesem Ordner installiert;
`vendor/` bleibt ignoriert:

```bash
composer install --no-dev --optimize-autoloader
```
