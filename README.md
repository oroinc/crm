OroNavigationBundle
========================
The `OroNavigationBundle` add ability to define menus in different bundles with builders or YAML files
to the [KnpMenuBundle](https://github.com/KnpLabs/KnpMenuBundle). It is also has integrated support of
ACL implementation from Oro UserBundle.

**Basic Docs**

* [Installation](#installation)
* [Your first menu](#first-menu)
* [Rendering Menus](#rendering-menus)

<a name="installation"></a>

## Installation

### Step 1) Get the bundle and the library

Add on composer.json (see http://getcomposer.org/)

    "require" :  {
        // ...
        "oro/OroMenu": "dev-master",
    }

### Step 2) Register the bundle

To start using the bundle, register it in your Kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Oro\Bundle\NavigationBundle\OroNavigationBundle(),
    );
    // ...
}
```

<a name="first-menu"></a>

## Your first menu

### Defining menu with PHP Builder

To create menu with Builder it have to be registered as oro_menu.builder tag in services.yml
alias attribute should be added as well and will be used as menu identifier.

```yaml
services.yml
parameters:
  acme.main_menu.builder.class: Acme\Bundle\DemoBundle\Menu\MainMenuBuilder

services:
  acme.menu.main:
    class: %acme.main_menu.builder.class%
    tags:
       - { name: oro_menu.builder, alias: main }
```
All menu Builders must implement Oro\Menu\BuilderInterface with build() method. In build() method Bundles manipulate
menu items. All builders are collected in ChainBuilderProvider which is registered in system as Knp\Menu Provider.
ChainBuilderProvider also include ConfigurationBuilder which leverages menu items with information from oro_menu.yml
configuration files. Configurations are collected in Extension and passed into Configuration class. In future more
addition Configurations may be created, for example for getting menu configurations from annotations or some persistent
storage like database. After menu structure created oro_menu.configure.<menu_alias> event dispatched, with MenuItem
and MenuFactory available.

``` php
<?php
// Acme/Bundle/DemoBundle/Menu/MainMenuBuilder.php

namespace Acme\Bundle\DemoBundle\Menu;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

class MainMenuBuilder implements BuilderInterface
{
    public function build(ItemInterface $menu, array $options = array(), $alias = null)
    {
        $menu->setExtra('type', 'navbar');
        $menu->addChild('Homepage', array('route' => 'oro_menu_index', 'extras' => array('position' => 10)));
        $menu->addChild('Users', array('route' => 'oro_menu_test', 'extras' => array('position' => 2)));
    }
}
```

### Menu declaration in YAML
YAML file with default menu declaration is located in /Oro/NavigationBundle/Resources/config/menu.yml.
In addition to it, each bundle may have their own menu which must be located in /SomeBundleName/Resource/menu.yml.
Both types of declaration files have the same format:

```yaml
oro_menu_config:
    templates:
        <menu_type>:                          # menu type code
            template: <template>              # path to custom template for renderer
            clear_matcher: <option_value>
            depth: <option_value>
            currentAsLink: <option_value>
            currentClass: <option_value>
            ancestorClass: <option_value>
            firstClass: <option_value>
            lastClass: <option_value>
            compressed: <option_value>
            block: <option_value>
            rootClass: <option_value>
            isDropdown: <option_value>

    items: #menu items
        <key>: # menu item identifier. used as default value for name, route and label, if it not set in options
            aclResourceId                     # ACL resource Id
            translateDomain: <domain_name>    # translation domain
                translateParameters:          # translation parameters
            label: <label>                    # label text or translation string template
            name:  <name>                     # name of menu item, used as default for route
            uri: <uri_string>                 # uri string, if no route parameter set
            route: <route_name>               # route name for uri generation, if not set and uri not set - loads from key
                routeParameters:              # router parameters
            attributes: <attr_list>           # <li> item attributes
            linkAttributes: <attr_list>       # <a> anchor attributes
            labelAttributes: <attr_list>      # <span> attributes for text items without link
            childrenAttributes: <attr_list>   # <ul> item attributes for nested lists
            showNonAuthorized: <boolean>      # show for non-authorized users
            display: <boolean>                # disable showing of menu item
            displayChildren: <boolean>        # disable showing of menu item children

    tree:
        <menu_alias>                            # menu alias
            type: <menu_type>                   # menu type code. Link to menu template section.
            extras:                             # extra parameters for container renderer
                brand: <string>
                brandLink: <string>
            children:                           # submenu items
                <links to items hierarchy>
                position: <integer>             # menu item posiotion
```

Configuration builder reads all menu.yaml and merges its to one menu configuration. Therefore, developer can add or
replace any menu item from his bundles. Developers can prioritize loading and rewriting of menu's configuration
options via sorting bundles in AppKernel.php.

<a name="rendering-menus"></a>

## Rendering Menus

To use configuration loaded from YAML files during render menu, twig-extension with template method oro_menu_render
was created. This renderer takes options from YAML configs ('templates' section), merge its with options from method
arguments and call KmpMenu renderer with the resulting options.

```html
{% block content %}
    <h1>Example menu</h1>
    {{ oro_menu_render('navbar') }}
    {{ oro_menu_render('navbar', array('template' => 'SomeUserBundle:Menu:customdesign.html.twig')) }}
{% endblock content %}
```