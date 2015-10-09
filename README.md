# clio
This package is a re-imagining of the wonderful League package [Climate](http://climate.thephpleague.com/), except written in strict [Hack](http://hacklang.org).

## WHY?!
I would really like to have useful tools for Hack.  The primary advantage I see that Hack has over PHP is the static type checker.  For the type checker to be useful, there needs to be statically typed libraries.

## Usage

**The API is unstable**

For now I will list example code.  This is one way in which I design my public facing APIs.

### Construction

```php
// Default input and output; same as not passing any input/output handlers to clio constructor
$input = new HackPack\Clio\StreamReader(STDIN);
$output = new HackPack\Clio\StreamWriter(STDOUT);
$clio = new HackPack\Clio\Clio($input, $output);
```

### Parameters/Options

```bash
hhvm task.php "Some Call Me Tim" --with-title -t "Tim The Wizard"
```
```php
// task.php
$clio = new HackPack\Clio\Clio();
$name = $clio->param('name')
             ->described('Description of the "name" argument.')
             ->required();
$titleFlag = $clio->flag('with-title')
$title = $clio->option('title')->aka('t')
```
## Contributing

I would love to have feedback.  Pull requests and issues are welcome!
