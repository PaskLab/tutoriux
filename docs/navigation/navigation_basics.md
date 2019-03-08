Navigation Basics
=========================

In the scope of this project, the term "Navigation" define any mechanism that provide a way to navigate or give information on the website section structure.

Those mechanism could be a **menu**, **breadcrumbs**, **page title**, **previous & next links**, etc.

### Navigation Element

Any object could represent a navigation element. But to do so, it has to implement the `NavigationElementInterface` class.

A navigation element must define methods for the following properties:

- **elementName** - Name that should be displayed
- **selectedElement** - Should be `true` if this is the current browsed element
- **parentElement** - Parent element
- **childrenElements** - Children elements
- **route** - String that represent the target route name
- **routeParams** - Array that contain route parameters
- **elementIcon** - Optional field that can contain a reference to an icon

A trait (`NavigationElementTrait`) can be used to add the properties and methods to a class implementing the `NavigationElementInterface`.

## Navigation Builder

The `NavigationBuilder` simply take's a multi-root array tree filled with `NavigationElement` as input and a selected element.

Once built, each level are assigned an incremental number that mostly is an helper for the graphic interface.

If a **selected element** is assigned, it's whole chain of parent elements will be marked as selected.

*One last thing*, the `NavigationBuilder` will ensure that the parent element is set on children elements if not.
But the child part of the association is mandatory, which mean that when your building the primary tree, you have to start at the root, not the other way around.