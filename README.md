<p align="center">
    <h1 align="center">Apache Logs Aggregator</h1>
    <br>
</p>

Задача выполнена на Yii2 Advanced, frontend модуль не используется.
Авторизация, просмотр данных и API находятся в backend части.

Применение миграций:
```
php yii migrate
```

Пример конфигурации файла настроек:
-------------------
Файл располагается в common/config/settings.php:
```
    'logs' => [
        '/var/www/tmp/log_1',
        '/var/www/tmp/log_2',
        '/var/www/tmp/log_3',
        ...
    ],
    'formatLogs' => 'combined',
    'fileMask' => 'access.log',
    'timezone' => '+0600',
    'parsePeriod' => [
        'min' => '*',
        'hour' => '*',
        'dayMonth' => '*',
        'month' => '*',
        'weekDay' => '*',
    ]
```

- `logs` - расположение логов
- `formatLogs` - вид логов (combined, common)
- `fileMask` - маска файлов
- `timezone` - ваша временная зона для корректного просмотра данных
- `periodParse` - интервал для разбора логов (синтаксис крона)

После конфигурации файла настроек, из консоли запускаем разбор файлов командой:
```
php yii install/run
```
Если файл настроек был измёнен, то для вступления в силу измнений нужно ещё раз запустить разбор.

Для остановки разбора файлов используем команду:
```
php yii install/stop
```