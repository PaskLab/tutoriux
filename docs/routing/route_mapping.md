Route Mapping
=========================

The following document describe how our custom router use the mapping 
entity entries to generate what we call "**Dynamic Routes**". See 
[modules documentation](../module/module_basic.md) for more information 
on how to sections and mapping are related.

## The mapping process

The distribution use a hierarchy of sections (this is a synonym for pages).
In a typical web application, each of those section must land in a given 
action of a controller. Usually in Symfony2, we use the standard routing 
to achieve this, but the dynamic side of the sections hierarchy we use 
in this CMS makes it complicated to handle. This is where the mapping 
process comes into play.

### Mapping types

There are different types of mapping:

- [A section to a route](#a-section-to-a-route)
- [A section to multiple routes](#a-section-to-multiple-routes)
- [A section to a controller](#a-section-to-a-controller)
- [A section to a template](#a-section-to-a-template)
- [A section to an URL](#a-section-to-an-url)
- [A section to a raw statement](#a-section-to-a-raw-statement)

### A section to a route

The most common mapping type is when you want to connect a section to a single route.
Let's say you want to connect the `Products` section to the `tutoriux_product_list` route.

#### route definition
```yml
tutoriux_product_list:
    pattern:  /{sectionsPath}/list
    defaults: { _controller: "ProductBundle:Product:list" }
```

(In the following data examples, [translation support](todo) is ignored and only used columns are displayed for the sake of simplicity)

#### section table

| id            | name          | slug
| ------------- | ------------- | ------
| 15            | Products      | products

#### mapping table

| section_id    | type   | target
| ------------- | ------ | ---------
| 15            | route  | tutoriux_product_list

The mapping entry read as follow: Connect the `tutoriux_product_list` route to the `products` section.

When the router process this mapping entry, it does two important things. First, it clone and rename the route to `section_id_15` and inject the tutoriux request attributes. The other important processing is the expansion of the {sectionPath} placeholder that gets replaced with the path of the section. The final result look like this:

```yml
section_id_15:
   pattern:  /products/list
   defaults: { _controller: "ProductBundle:Product:list" }
```

### A section to multiple routes

Another common mapping type is to map multiple routes to a section. This scenario is siminal to the single route mapping but it introduce a new option named `mapping_alias`

Let's say you want to map those routes to the `product` section having the id 15:

```yml
tutoriux_product_list:
   pattern:  /{sectionsPath}
   defaults: { _controller: "ProductBundle:Product:list" }

tutoriux_product_category:
   pattern:  /{sectionsPath}/category/{categorySlug}
   defaults: { _controller: "ProductBundle:Product:category" }

tutoriux_product_detail:
   pattern:  /{sectionsPath}/{productSlug}
   defaults: { _controller: "ProductBundle:Product:detail" }
```

If you apply the previous single route mapping technique the results will be as follow:

```bash
$ app/console router:debug
...
section_id_15            ANY    ANY    ANY  /products/{productSlug}
```

See the problem? Only the last mapped route got generated. This is because of a collision in the route names. The solution is to use an option called `mapping_alias` in the route definition. This option will be appended to the route name, making them unique.

```yml
tutoriux_product_list:
   pattern:  /{sectionsPath}
   defaults: { _controller: "ProductBundle:Product:list" }

tutoriux_product_category:
   pattern:  /{sectionsPath}/category/{categorySlug}
   defaults: { _controller: "ProductBundle:Product:category" }
   options:  { mapping_alias: "category" }

tutoriux_product_detail:
   pattern:  /{sectionsPath}/{productSlug}
   defaults: { _controller: "ProductBundle:Product:detail" }
   options:  { mapping_alias: "detail" }
```

Now the results look like:

```bash
$ app/console router:debug
...
section_id_15            ANY    ANY    ANY  /products/{productSlug}
section_id_15_category   ANY    ANY    ANY  /products/category/{categorySlug}
section_id_15_detail     ANY    ANY    ANY  /products/details/{categorySlug}
```

### The {sectionPath} placeholder

When a route is mapped to a section, a special placeholder if used to inject the path of the mapped section. The path of a section is made of the section slug and prefixed with every parents slugs.

For example given these values:

| id  | parent_id | name          | slug
| --- | --------- | ------------- | ------
| 3   | NULL      | Our Company   | our-company
| 4   | 3         | Contact Us    | contact-us

Since the `Contact Us` section has a parent, the sectionPath will be `/our-company/contact-us`.

### A section to a controller

Type = render

### A section to a template

type = include

### A section to an URL

type = url

### A section to a raw statement

type = raw