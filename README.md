# simple-acl
A simple and highly flexible and customizable access control library for PHP 

Ideas / Work in Progress

```
Simple Acl

AclUser implements AclUserInterface
	id (could be string or int)
	groups (an instance of AclGroups which internally contains an array of AclGroup objects)
	__construct(string|number $id, AclGroups ...$groups)

*AclUserInterface
	getUserId(): string|number
	getGroups(): AclGroups
	
*AclUserOwnableInterface
	getOwnerId()
	
AclGroup implements AclGroupInterface
	id (could be string or int)
	tags (an instance of AclGroupActionTags which internally contains an array of AclGroupActionTag objects)
	
*AclGroupInterface
	getGroupId(): string|number
	getActionTags(): AclGroupActionTags
	getSortValue(): int value (higher value means group has higher priority)
	
AclGroupActionTag implements AclGroupActionTagInterface
	tag (string describing an action a group can perform)
	__construct(string $tag)
	
* AclGroupActionTagInterface
	getTag(): string describing an action a group can perform
	getSortValue(): int value (higher value means actionTag has higher priority)
	
abstract class GenericCollection implements  \ArrayAccess, \Countable, \IteratorAggregate
{
	protected $values;

    /**
     * 
     * ArrayAccess: does the requested key exist?
     * 
     * @param string $key The requested key.
     * 
     * @return bool
     * 
     */
    public function offsetExists($key)
    {
		return array_key_exists($key, $this->values);
    }
    
    /**
     * 
     * ArrayAccess: get a key value.
     * 
     * @param string $key The requested key.
     * 
     * @return mixed
     * 
     */
    public function offsetGet($key)
    {
        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        } else {
            throw new \Exception("offsetGet({$key})");
        }
    }
    
    /**
     * 
     * ArrayAccess: set a key value.
     * 
     * @param string $key The requested key.
     * 
     * @param string $val The value to set it to.
     * 
     * @return void
     * 
     */
    public function offsetSet($key, $val)
    {
		$this->values[$key] = $val;
    }
    
    /**
     * 
     * ArrayAccess: unset a key.
     * 
     * @param string $key The requested key.
     * 
     * @return void
     * 
     */
    public function offsetUnset($key)
    {
        $this->values[$key] = null;
        unset($this->values[$key]);
    }
  
  
	public function toArray() : array {
		return $this->values;
	}

	// IteratorAggregate
	public function getIterator() {
		return new \ArrayIterator($this->values);
	}
	
    // Countable: how many keys are there?
    public function count()
    {
        return count($this->values);
    }
}

class AclGroups extends GenericCollection
{
  public function __construct(AclGroup ...$groups) {
    $this->values = $groups;
  }

}

class AclGroupActionTags extends GenericCollection
{
  public function __construct(AclGroupActionTag ...$tags) {
    $this->values = $tags;
  }

}

AclUserAssertionInterface
	allowActionForGroup(AclGroupActionTagInterface $action_tag,  AclGroupInterface $group)
	allowActionForGroups(AclGroupActionTagInterface $action_tag,  AclGroupInterface ...$group)
	denyActionForGroup(AclGroupActionTagInterface $action_tag,  AclGroupInterface $group)
	denyActionForGroups(AclGroupActionTagInterface $action_tag,  AclGroupInterface ...$group)
	
	isUserAllowedToPerformAction(AclGroupActionTagInterface $action_tag): bool
	
	isUserAllowedByOwnership(AclUserInterface $user, AclUserOwnableInterface $ownable): bool
```
