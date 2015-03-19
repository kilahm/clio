Command line argument/option parsing
====================================

Clio allows you to easily define and access any number of arguments (required or optional) and options (indicated with `-` or `--`, and are always optional).
Arguments are taken from the invocation of your script.  The entire command is split on non-quoted space characters.

```
$ myscript.php -a arg1 --long-option arg2 arg3 -more --options
```

The above script invocation would produce the following items as generic arguments (i.e., are the elements of `$_SERVER[‘argv’]`)

* myscript.php
* -a
* arg1
* --long-option
* arg2
* arg3
* -more
* --options

## Command line arguments

To easily access any arguments the user passed to the initial call of your script, simply call `getArgVals()`.

```
#!hhvm
<?hh
var_dump($clio->allArguments());
```

```
$ myscript.php myArg1 myArg2
object(HH\Map)#1 (2) {
  [“1”]=>
  string(6) “myArg1”
  [“2”]=>
  string(6) “myArg2”
  }
}
```

Note that the result is a `Map<string,string>`.  This is because you can make named arguments, which then become required arguments.

```
#!hhvm
<?hh
$clio->arg(‘test’);
var_dump($clio->getArgVals());
```

```
$ myscript.php myArg1 myArg2
object(HH\Map)#1 (2) {
  [“test”]=>
  string(6) “myArg1”
  [“2”]=>
  string(6) “myArg2”
  }
}
```

You may also save the argument object to be passed around later.

```
#!hhvm
<?hh
$testArg = $clio->arg(‘test’);
var_dump($testArg->value());
```

```
$ myscript.php myArg1 myArg2
string(6) “myArg1”
```

See the `Argument` class for more details.

## Command line options

There are two types of options: flags and options.  Flags do not accept values while options do accept values.

To define an option or a flag, invoke `$clio->option(‘name’)` or `$clio->flag(‘name’)` respectively. `’name’` is the string used to identify the option when invoking your script.

```
#!hhvm
<?hh
$option = $clio->opt(‘name’);
$flag = $clio->flag(‘other-name’);
```

The above script would recognize the option `--name` and the flag `--other-name`.

Option, flag, and argument names may be composed of letters, dash (`-`), and underscore (`_`).  The first character of the option name must be a letter.

### Flags

Flag options are the simplest and the default.  They have no value and are merely present or not.

```
#!hhvm
<?hh
$flag = $clio->flag(‘name’);
var_dump($flag->wasPresent());
```

```
$ myscript.php --name; myscript.php;
bool(true)
bool(false)
```

You may also ask the flag how many times it was present in the invocation of the script.

```
#!hhvm
<?hh
$flag = $clio->flag(‘f’);
var_dump($flag->occurances());
```

```
$ myscript.php -ff; myscript.php -f;
int(2)
int(1)
```

### Options

Options may accept values, and will do so if possible.  Notice in the next example that the text before the option is interpreted as an argument to the script, but the text after is
interpreted as the value of the option.

```
#!hhvm
<?hh
$option = $clio->option(‘opt’);
var_dump($clio->allArguments(), $option->value());
```

```
$ myscript arg --opt val
object(HH\Map)#1 (2) {
  [“1”]=>
  string(3) “arg”
}
string(3) “val”
```

If an option does not have a valid value, the value will revert to its default, or an empty string if no default was set.

### Long and Short Option Names

If the name of the option is a single letter, it is considered a “short option” which may be set with a single dash character.  Multiple short flags may be set by
chaining all of the letters together after a single dash.

```
$ myscript -abcef -er
```

The above script invocation would set flags a, b, c, e, f, e, and r (recall that a single option or flag may appear multiple times).

If the name of an option is more than one letter, it is a “long option” which may be set with two dash characters.  You may only set one long option per double dash.

```
$ myscript --long-optionA --other_long_option
```

The above script invocation would set flags long-optionA and other\_long\_option.

Short option values will either be the rest of the current argument, or the entirety of the following argument as long as the following argument does not start with a dash.
Long option values may either be separated from the option name by `=` or may be the following argument.

```
$ myscript.php -na Name; myscript.php -anName;
string(1) “a”
string(4) “Name”
```

The first time the script is invoked, the short option `n` is first in the list.  The rest of the argument is interpreted as the value of the option, which is the single
character `”a”`.  The second time, the flag `a` is set, then the option `n` is encountered.  The rest of the argument, and thus the value of the option is the string `”Name”`.

```
$ myscript.php --name Name; myscript.php --name=”Name”
string(4) “Name”
string(4) “Name”
```

Both invocations produce the same value for the option.  The value does not include the quotes in the second case because bash parses them before the arguments are passed to your script.
If your shell treats quotes differently, then you should account for that in your script.

Value options may also be given a default value.  Simply pass a string into the `withValue()` method.

```
#!php
$option = $clio->opt(‘name’)->withValue(‘Default name’)
```

### Option aliases

Each option and flag has a primary name and may have any number of aliases.

```
#!php
<?hh

$option = $clio->opt(‘long-name’)->aka(‘a’);
var_dump($option->wasPresent());
```

```
$ myscript.php -a; myscript --long-name;
bool(true)
bool(true)
```

