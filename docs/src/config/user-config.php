<section id="user-config" title="User Config">
  <div class="page-header">
    <h1>User Config</h1>
  </div>

    <p class="lead">Tell Shard who is using documents.</p>

    <p>Several Shard extensions need to know who the current authenticated user is and what their roles are be. If the Shard extensions you are using don't need a configured user, then you can skip this bit. The documentation for each extension will note if a user is required.</p>

    <p>If you need a configured user, first you need to create a user class.</p>

    <h2>Creating a simple user class</h2>
    <p>A simple user should implement the <code>Zoop\Common\User\UserInterface</code>, and may use the <code>Zoop\Shard\User\DataModel\UserTrait</code>. Eg:</p>

<pre class="prettyprint linenums">
use Zoop\Common\User\UserInterface;
use Zoop\Shard\User\DataModel\UserTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class User implements UserInterface {

    use UserTrait;
}
</pre>

    <h2>Creating a role aware user class</h2>
    <p>A role aware user is a further requirement of some extensions. It must also implement the <code>Zoop\Common\User\RoleAwareUserInterface</code>, and may use the <code>Zoop\Shard\User\DataModel\RoleAwareUserTrait</code>. Eg:</p>

<pre class="prettyprint linenums">
use Zoop\Common\User\UserInterface;
use Zoop\Common\User\RoleAwareUserInterface;
use Zoop\Shard\User\DataModel\UserTrait;
use Zoop\Shard\User\DataModel\RoleAwareUserTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class RoleAwareUser implements UserInterface, RoleAwareUserInterface {

    use UserTrait;
    use RoleAwareUserTrait;
}
</pre>

    <h2>Configure the user service</h2>

    <p>The <code>user</code> service must be configured to return an instance of your user class.</p>

    <p>For example you can configure your service as a closure factory:</p>
<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'service_manager_config' => [
        'factories' => [
            ...
            'user' => function(){
                $user = new RoleAwareUser();
                $user->setUsername('toby'); //set the username
                $user->addRole('admin');    //add any roles the user has
                return $user;
            }
        ]
    ]
]);
</pre>

    <p>For example you can configure your service with a factory class:</p>
<pre class="prettyprint linenums">
$manifest = new Zoop\Shard\Manifest([
    ...
    'service_manager_config' => [
        'factories' => [
            ...
            'user' => 'My\Active\User\Factory'
            }
        ]
    ]
]);
</pre>

    <p>Or, you may wish to set the <code>user</code> service directly:</p>

<pre class="prettyprint linenums">
$user = new RoleAwareUser();
$user->setUsername('toby'); //set the username
$user->addRole('admin');    //add any roles the user has

$manifest = new Zoop\Shard\Manifest([...]);
$manifest->getServiceManager()->setService('user', $user);
</pre>
</section>
