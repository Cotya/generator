Cotya/Generator
===============

[![Join the chat at https://gitter.im/Cotya/generator](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/Cotya/generator?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

*generates you whole modules(magento,more) from more or less then an xml file*
    


**To describe it, let me steal this Question:**  
  
    how does this compare to n98 magerun code generation and/or the ultimate module creator extension?

It compares not very well because of the very early stage it is in.  
It differs from them, as it uses an xml document as base instead of a cli or web UI.
Also this document is defined framework agnostic. 
The adcentage will be, that you can generate a magento 1 or 2 module from the same base, and in theory even a symfony one.  
Another adventage of a standardized format will be the possibility of easy buildable custom UIs for define new modules,
as they only need to work with a simplified xml document.

But thats only theory yet, help and put this into practice.



##How Can I help?

We need to define a Schema/Standard for the base xml, and we also need Ideas for features
(different things which could or should be able to generate from simple xml)


##How can I test it

1. Check out the Repository via git
2. `composer.phar install`
3. try out the stuff in the examples directory

## The schema

the `schema/defined/` directory of this project trys to list and describe all the possible parts of the xml,
to define a new module.
Every Tag has an own file, and if it can have child tags,
we have analog a directory with files for every Tag allowed inside this Tag.

Tags which are not finalized yet and still need work/review/voting are added in `schema/draft/` 
with identical directory structure as described before for `schema/defined/` 

We may have later a real xml schema file, but its to early for this, also I would need help,
as I have no experience with xml schemas yet.


