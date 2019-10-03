Routing
=========================

The routing component is a key part of the tutoriux project.
This component does three main things:

- Generate mapped routes based on entries in the mapping table.
- Inject extra attributes into mapped routes to provide context information.
- Expand the {sectionPath} placeholder.

### Configuration

The application uses 2 different loader to import mapped route:
- The `MappingDirectoryLoader` class
- The `MappingYamlFileLoader` class

The `MappingDirectoryLoader` will import all Yaml files found under 
*.../config/routes/mapping/* and use the `MappingYamlFileLoader` to parse 
their content.

You can still use standard symfony route by using route definitions 
outside */mapping/* directory.

### Loaders behavior

1. The loader will ignore any route definition under */mapping/* 
if the `name` argument doesn't match any mapping.
2. The loader will ignore any *__route type__* mapping that doesn't 
match any route name.
3. The loader will omit section with field `remove_from_url` set to 
`true` when building the expanded path. Using this functionality is nice 
for SEO, but **be careful** since you could end-up with two route 
defining the same path.
4. The custom loader currently display no error for unmatched mapping. 
This feature might be implemented in the future.

## What is a tutoriux enabled request ?

A tutoriux enabled request is a normal symfony request with some 
specials attributes injected into the route that will trigger the 
bootstrapping of all tutoriux related services.

### Attributes structure

There is three key attributes: `_tutoriuxContext`, `_tutoriuxEnabled` 
and `_tutoriuxRequest`.
Here is a typical structure:

```php
array(
     '_tutoriuxContext' => 'site',
     '_tutoriuxEnabled' => true,
     '_tutoriuxRequest' => [
         'sectionId' => 1,
         'sectionSlug' => 'current-section-name',
         'sectionsPath' => 'parent-section/child-section/current-section-name'
     ]
)
```

### Creating an tutoriux enabled request

Attributes are automatically injected when a route is mapped to a 
section. The mapping process is covered [here](route_mapping.md).

### Hardcoded/Manual mapping

Although this is not the recommended method, you can manually create a 
compatible request straight from any routes definitions:

```yml
tutoriux_product_detail:
   pattern:  /{sectionsPath}/{productSlug}
   defaults:
      _controller: "ProductBundle:Frontend/Product:detail"
      _tutoriuxContext: 'cms'
      _tutoriuxEnabled: true
      _tutoriuxRequest:
         sectionId: 1
         sectionSlug: 'current-section-name'
         sectionPath: 'parent-section/child-section/current-section-name
```

The drawback of this method is that every parameters are hardcoded into 
the route definition, this method is only useful in rare special cases.

### Booting of a tutoriux enabled request

All of the booting process is done in a 
[listener](../../src/Listeners/ControllerListener.php) that looks for 
the presence of the tutoriux attributes in the current request. If those 
attributes are found and valid then the application core is booted and 
the normal request flow continues.

The *ApplicationCore* is a service that take care of gathering and 
building some useful information as:
- Fetching the current **section entity**.
- Building the navigation elements tree.
- Building Breadcrumbs.
- Creating PageTitle.
- Managing the current locale and editLocale.
- Defining the **editLocaleEnabled** property.
- Storing Request, Session, Doctrine and DoctrineInit services.

### More in depth

The main purpose of tutoriux request is to provide context information 
regarding the section related to the path we are currently browsing.

##### About the `_tutoriuxContext property

The `_tutoriuxContext` parameter is used to tell the app is which app 
portion we stand. Different behavior could be wanted for each part of 
the website.

This parameter is actually used in the `ApplicationCore` service to avoid 
loading an inactive section while browsing a public portion of the application.

##### About the `_tutoriuxEnabled` property

The `ApplicationCore` service will be initiated by the 
`ControllerListener` only when this parameter is set to `true`.

However, there is *one exception*!

A hook exist in the "cms" context (`_tutoriuxContext: 'cms'`). The  
`ControllerListener` will create a tutoriux enabled request if the route 
contain the `sectionId` parameter. The purpose is to boot the 
`ApplicationCore` service when managing sections entities in the CMS.

### Tips

Split your routing into different folders named after the different 
portion of your application.

Routing resource loading example:

```
# ../config/routes/routing.yaml 

mapping:
  resource: 'mapping/'
  type: mapping_directory

site:
  resource: 'site/'
  type: directory
  prefix:
    en: ''
    fr: '/fr'
  defaults:
    _tutoriuxContext: 'site'

cms:
  resource: 'cms/'
  type: directory
  prefix: /cms
  defaults:
    _tutoriuxContext: 'cms'
```   