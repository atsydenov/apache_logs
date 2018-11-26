<p align="center">
    <h1 align="center">Apache Logs Aggregator</h1>
    <br>
</p>

Приложение является агрегатором данных из access логов apache с сохранением в БД.
Выполнено на Yii2 Advanced, frontend модуль не используется.
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

Примеры использования API (предполагаем, что `http://app.local -> ../backend/web/`, то есть root сервера установлен в папку `../backend/web/`):
```
    http://app.local/api - полный список логов
    http://app.local/api?sort=ip - сортировка по ip (ASC)
    http://app.local/api?sort=-ip - сортировка по ip (DESC)
    http://app.local/api?fd=0&td=100 - получение выборки по временному интервалу в unix формате (from date, to date)
    http://app.local/api?group=ip - группировка по ip
    http://app.local/api?per-page=10 - получение 10 записей на страницу (по умолчанию 100)
```
