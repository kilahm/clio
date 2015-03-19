Output Formatting
=================

Clio is able to format your text before it is sent to the terminal.

```
#!hhvm
<?hh
use kilahm\Clio\Format\Style;

echo $clio->style(‘Some string’)->with(Style::error());
```

The above would print the text `Some string` with the pre-defined error styling (red background, white text).  This is achieved through the use of ANSI control codes.
For a full list of the possible codes look at the code for the `Style` class.
