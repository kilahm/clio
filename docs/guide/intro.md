Using Clio
==========

Hack Clio strives to presents a fluent and straightforward API to allow your script to interact with its user through an ANSI compliant terminal. User prompts and
other formatted output is handled through the use of ANSI escape codes.

## Installing `Clio`

The only supported method for instantiating `Clio` is through [Composer](https://getcomposer.org/).  Simply add the following line to the require block of your composer.json file:
```
“kilahm/clio” : “0.1.*”
```

## Instantiating `Clio`

The recommended way of instantiating the Clio class is through its default factory method.  This will extract `$_SERVER[‘argv’]`, inject the default argument parser, and connect the object
to stdin and stdout.

```
#!hhvm
<?hh // strict
$clio = Clio::fromCli();
```

If you wish to supply your own set of arguments for Clio to parse, you may instantiate the class directly.  This method is currently unsupported, though you may look at the code to
to see the required dependencies to be injected.

## Format of examples

All of the example code below assumes there is a variable named `$clio` with an instance of `Clio` obtained through the factory method as described above.

Often there will be two code blocks.  The top one is typically an excerpt from a hack script and the bottom one is invoking the script from the command line along with what is sent to
`STDOUT`.  The name of the script in all of the examples is `myscript.php`.

