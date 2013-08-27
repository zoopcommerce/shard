<?php ob_start()?>

<!-- Subhead
================================================== -->
<header class="jumbotron subhead" id="overview">
  <div class="container">
    <h1>Custom Extensions</h1>
    <p class="lead">Create your own Shard Extensions.</p>
    <p>Follow this tutorial to create a simple extension that defines a Color annotation that can be added to a document and can filter by color.</p>
  </div>
</header>


  <div class="container">

    <!-- Docs nav
    ================================================== -->
    <div class="row">
      <div class="span3 bs-docs-sidebar">
        <ul data-dojo-type="havok/widget/NavList"
            data-dojo-mixins="havok/widget/_AffixMixin, havok/widget/_ScrollSpyMixin"
            data-dojo-props="
               linkTemplate: '&lt;a role=&quot;navitem&quot; href=&quot;${href}&quot;&gt;&lt;i class=&quot;icon-chevron-right&quot;&gt;&lt;/i&gt; ${text}&lt;/a&gt;',
               viewportOffset: {top: 40, bottom: 0},
               affixTarget: 'mainContent',
               spyTarget: 'mainContent'
            "
            class="nav-stacked bs-docs-sidenav"
        >
        </ul>
      </div>
      <div class="span9" id="mainContent">

        <?php
        include 'custom-extensions/extension-class.php';
        include 'custom-extensions/extension-factory.php';
        include 'custom-extensions/annotation.php';
        include 'custom-extensions/annotation-subscriber.php';
        include 'custom-extensions/filter.php';
        include 'custom-extensions/using-it.php';
        include 'custom-extensions/share-it.php';
        ?>

      </div>
    </div>
  </div>
<?php
$content = ob_get_clean();
include 'layout.php';
?>
