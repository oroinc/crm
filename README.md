poc-product-entity-design
=========================

POC on product entity design to illustrate attribute management

TODO
====

- use translatable interface / use a custom translation mecanism in place of doctrine gedmo extension

- use a distinct attribute manager (not mixed with product manager)

- think about value representation (should be loaded in product as key/value)

- demo on options usage

- sanitize object query results 

- clean way to play with backend type / many tables ?

- complete product / flexibleentity repository

- should be use an extended Doctrine\ORM\Persisters\ to deal with findBy customization ?

- enhance find($id) to load any values in one query ? (no lazy load when get each value)