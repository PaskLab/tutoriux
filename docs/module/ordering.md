Ordering
=========================

In this CMS, sections, modules and mappings are interrelated. These
entities possess an ordering field and applying  different numbers 
on them provide you a way to display their entries in a chosen order.

This document aim to hint you on how to use each ordering field. 

>**Pay a particular attention to the mapping ordering field!**

### Section ordering field

This one is pretty simple. It give you a way to rank your sections a
way that suit your needs. Your can change the order by accessing the
"**Manage Root Section**" part of the CMS.

For a more contextual ordering, you should look into ***mapping ordering
field***.

### Module ordering field

This one is mainly used to allow you displaying module entities in a
specific order for convenience. Since module are an internal
mechanism of the CMS, there is not a lot of use case for it.

### Mapping ordering field

Mapping entity entries are divided in distinct subgroup defined by the 
values of their properties.

Each different combination of `section`, `module` and `context` values 
must be considered separately when ordering a given set. 

***Caution**, 
failing to do so when creating an "ordering" update query could result 
in side effects on previously organized data.*

Mapping entries shall always be ordered withing their respective group. 

##### How mapping groups are defined?

To be of some use, a mapping entry needs to be related to either a 
`section` or a `module`. Or both of them at the same time. As for the 
`context`, it's merely a character string that allow you to separate 
concerns like CMS, website, API and others.

There are three main categories of groups:

* Mapping related to both a section and a module:
  * These entries can be managed under their related section page in the 
  CMS and will be grouped under subgroups based on their related 
  `module` and `context`.
* Mapping that are only related to a section:
  * These entries can be managed under their related section page in the 
  CMS and will be all grouped under the "No associated module" group.
* Mapping that are only related to a module:
  * Since these entries don't have any related section, they will be 
  grouped under subgroups based on their related `module` and `context` 
  in the "Manage Module Mapping" section of the CMS.