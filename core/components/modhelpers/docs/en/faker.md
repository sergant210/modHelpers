##faker
Creates a faked data using the [Faker](https://github.com/fzaninotto/Faker) library.

```faker($property = '', $locale = '')```
- $property (string) - image url. 
- $locale (string) - locale. One of these values - 'ru_RU', 'en_US', 'de_DE', 'fr_FR'. Optional.
```html
$imageUrl = faker(['imageUrl'=>[200,100]]);
# Use Fenom template engine. 
<!-- resource content -->
<p>{faker(['text'=>[700]])}</p>
<p>{faker(['text'=>[500]])}</p>
<p>{faker(['text'=>[1000]])}</p>
```