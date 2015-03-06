Этот набор предназначен для автоматизации рутинных процессов загрузки файлов их сохранения и хранения.
Он включает в себя:
- Виджет для загрузки файлов
- Компонент для сохранения файлов (построен поверх flysystem)
- Действия для загрузки, удаления и просмотра (скачивания файлов)
- Поведение для сохранения файлов в модели и удаления файлов при удалении модели

# File Storage
Для работы с File Kit вам надо сконфигурировать FileStorage. Этот компонент представляет собой слой абстракции над файловой системой 
- его основная задача брать на себя генерацию уникального имени для каждого файла и создавать события записи, удаления и тд.
```
'fileStorage'=>[
    'class' => 'trntv\filekit\Storage',
    'baseUrl' => '@web/uploads'
    'filesystem'=> ...
        // OR
    'filesystemComponent' => ...    
],
```
Есть несколько возможностей настроить Storage для работы с `flysystem`.
1. Создать класс построитель, реализующий `trntv\filekit\filesystem\FilesystemBuilderInterface` и реализовать в нем метод `build`
который возвращает объект файловой системы.
Exmaple:
```php
namespace app\components;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;
use trntv\filekit\filesystem\FilesystemBuilderInterface;

class LocalFlysystemBuilder implements FilesystemBuilderInterface
{
    public $path;

    public function build()
    {
        $adapter = new Local(\Yii::getAlias($this->path));
        return new Filesystem($adapter);
    }
}
```
Конфиг в таком случае будет выглядеть следующим образом:
```
'fileStorage'=>[
    ...
    'filesystem'=> [
        'class' => 'app\components\FilesystemBuilder',
        'foo' => 'bar'
        ...
    ]
],
```
Подробнее о том что должен представлять из себя метод `build` вы можете посмотреть в документации http://flysystem.thephpleague.com/

2. Использовать сторонее расширение, например `creocoder/yii2-flysystem`, и в параметр `filesystemComponent` передать именя сконфегурированого компонента
файловой системы
Конфиг в таком случае будет выглядеть следующим образом:
```
'fs' => [
    'class' => 'creocoder\flysystem\LocalFilesystem',
    'path' => '@webroot/files'
    ...
],
'fileStorage'=>[
    ...
    'filesystemComponent'=> 'fs'
],
```
# Actions
File Kit содержит несколько заготовленных Actions для реализации работы с файлами.

### Upload Action
Предназначен для сохранение загруженных с помощью виджета файлов
```
public function actions(){
    return [
           'upload'=>[
               'class'=>'trntv\filekit\actions\UploadAction',
           ]
       ];
}
```
Описание параметров смотрите в corresponding class

### Delete Action
```
public function actions(){
    return [
       'delete'=>[
           'class'=>'trntv\filekit\actions\DeleteAction',
       ]
    ];
}
```
Описание параметров смотрите в corresponding class
**NOTE: Не забудьте сконфигурировать Access Rules для этого action

### View (Donwload) Action
```
public function actions(){
    return [
       'view'=>[
           'class'=>'trntv\filekit\actions\ViewAction',
       ]
    ];
}
```
Описание параметров смотрите в corresponding class

# Upload Widget
Отдельное использование
```
echo \trntv\filekit\widget\Upload::widget([
    'model'=>$model,
    'attribute'=>'files',
    'url'=>['upload'],
    'sortable'=>true,
    'maxFileSize'=>10000000, // 10 MiB
    'maxNumberOfFiles'=>3
]);
```

В сочетании с ActiveForm
```
echo $form->field($model, 'files')->widget(
    '\trntv\filekit\widget\Upload',
    [
        'url'=>['upload'],
        'sortable'=>true,
        'maxFileSize'=>10000000, // 10 MiB
        'maxNumberOfFiles'=>3
    ]
);
```

# FilesBehavior
Это поведение предназначено для сохранения загруженных файлов в соответствующем relation.

```php
 public function behaviors()
 {
     return [
          'file' => [
              'class' => 'trntv\filekit\behaviors\FilesBehavior',
              'filesAttribute' => 'files',
              'filesRelation' => 'uploadedFiles',
              
          ],
      ];
 }
```
Описание остальных параметров и пример схемы таблицы смотрите в corresponding class
