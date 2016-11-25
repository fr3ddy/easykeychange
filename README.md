[![Latest Stable Version](https://poser.pugx.org/fr3ddy/easykeychange/v/stable)](https://packagist.org/packages/fr3ddy/easykeychange)
[![Total Downloads](https://poser.pugx.org/fr3ddy/easykeychange/downloads)](https://packagist.org/packages/fr3ddy/easykeychange)
[![Latest Unstable Version](https://poser.pugx.org/fr3ddy/easykeychange/v/unstable)](https://packagist.org/packages/fr3ddy/easykeychange)
[![License](https://poser.pugx.org/fr3ddy/easykeychange/license)](https://packagist.org/packages/fr3ddy/easykeychange)
[![Monthly Downloads](https://poser.pugx.org/fr3ddy/easykeychange/d/monthly)](https://packagist.org/packages/fr3ddy/easykeychange)
[![Daily Downloads](https://poser.pugx.org/fr3ddy/easykeychange/d/daily)](https://packagist.org/packages/fr3ddy/easykeychange)

# Easytrans

!ALPHA RELEASE!

Easy to use Keychange for your multilanguale Laravel App.

Export Excel files, change keys and import it again.

It's as easy as it sounds...

Test it now!

# Installation
Require this package with composer
```
composer require fr3ddy/easykeychange
```

Add service provider to your app/config.php providers array
```php
Fr3ddy\Easykeychange\EasykeychangeServiceProvider::class,
```

Add Excel service provider to your app/config.php providers array
```php
Maatwebsite\Excel\ExcelServiceProvider::class,
```

Add Alias to your aliases array in your app/config.php
```php
'Excel' => Maatwebsite\Excel\Facades\Excel::class,
```

Publish config with
```
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
```
set "force_sheets_collection" = true (line 466)


# Usage
## Export
By using the following command in your project directory, an excel file will be generated in your storage/easykeychange folder.
```
php artisan easykeychange:export
```

Withing this excel file, you will find one sheet for each translation file existing for this language.
Feel free to remove any sheet, it will not be a problem when importing it again.


## Import
By using the following command in your project directoy, the excel file in the storage/easykeychange folder will be imported. As the filename I am expecting keys.xls
```
php artisan easykeychange:import
```

When importing, backup files are created, and new files are generated for all sheets in this excel based on the name of the sheet.

## Hint
This is working amazingly with potsky/laravel-localiziation-helpers and mcamara/laravel-localization
