# Things To Do
* ~~Implement Generic Classes implementing the various interfaces~~ [DONE]
* ~~Implement a profiling mechanism for debugging purposes that shows an audit trail of how permissions were calculated when isAllowed is invoked~~ [DONE]
* ~~Write unit tests~~ [DONE]
* ~~Hook up to travis and other code monitoring services~~ [DONE]
* ~~Update class diagram once package is stable~~ [DONE]
* Document using this package using acl examples from existing application and even using examples from the zend packages.
   * Add guidelines on how to customize this package to suit various requirements like the 
   Owner, User and Group level permission enforcement described above.
* Implement a separate package illustrating how to implement Owner, User and Group level permission enforcement
* Check other stuff in my other projects that could be of value in this one
* Add a logging mechanism to log how permissions are calculated in isAllowed to a string
   * This will require adding a getAuditTrail method to the collection interfaces and classes and also to the VersatileAcl class
       * When setLogger is called on an instance of VersatileAcl, it will inject that logger into every collection it creates
* ~~Submit to packagist once it's well done.~~ [DONE]
* ~~When PHP 7.4 becomes the minimum version, change all class properties to typed properties and edit **rector.php** to include PHP 7.4 rules~~ [DONE]

Add them to https://github.com/rotexsoft/versatile-acl/issues moving forward.
