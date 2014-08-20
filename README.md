extension is still under heavy development ;)
```
'fileStorage'=>[
    'class'=>'trntv\filekit\storage\FileStorage',
    'repositories'=>[
        'uploads'=>[
            'class'=>'trntv\filekit\storage\repository\FilesystemRepository',
            'basePath'=>'@webroot/uploads',
            'baseUrl'=>'@web/uploads',
        ]
    ],
],
```