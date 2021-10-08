# Foswig.js in PHP

_Foswig.js in PHP_ is, as its name suggests, the PHP version of a JavaScript library that allows you to easily create Markov chains based on arbitrary dictionaries to generate readable pseudo-random words.

## Example

```php
require_once 'foswig.php';

/*
Create the Markov chain and specify the order of the chain & input dictionary
The order (an integer that is greater than 0) indicates how many previous 
letters are taken into account when selecting the next one. A smaller order 
will result in a more randomized, less recognizeable output. Also, a higher 
order will result in words which resemble more closely to those in the original 
dictionary.
*/
$chain = new MarkovChain(3, [
  'red',
  'yellow',
  'blue',
  'brown',
  'orange',
  'green',
  'violet',
  'black',
  'carnation',
  'pink',
  'white',
  'dandelion',
  'cerulean',
  'apricot',
  'scarlet',
  'indigo',
  'gray',
  // You should add more words here
  // This list is too short to get good results
]);

/*
Generate a random word with a minimum of 4 characters, a maximum of 10 letters, 
and that cannot be a match to any of the input dictionaries words.
*/
$word = $chain->generate($minLength = 3, $maxLength = 6, $allowDuplicates = false);
```

## Caveats

- I did this as an exercise to learn PHP.
- Therefore, I can't guarantee that it is production-ready.
- The `start` attribute of the `MarkovChain` class has two `null` at the end of every node instead of one, but it doesn't seem to cause any bugs 🤷
- Not thoroughly tested.

## Based on

Original code: [mrsharpoblunto's foswig.js](https://github.com/mrsharpoblunto/foswig.js)
