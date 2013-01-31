# Supermodlr

## What is Supermodlr?
* Object Relational Mapper (MySQL, MS SQL)
* Object Document Mapper (MongoDB, CouchDB)
* Object Index Mapper (Solr,ElasticSearch)
* Automated Caching (Redis, Memcache)
* Standard Field Rule-set
    * Fields are related to models
    * Fields have rules that decide how they are stored, retrieved, viewed, etc
* Object Orientated Model/Field Definitions
    * Model_BaseballPlayer extends Model_Person
    * Extending Models inherits all fields from the parent
    * Traits (mixins) can be applied to models.  Traits are simply abstract models.
    * use Model_TraitCreatedUpdated could apply created and updated fields + logic to a model
* Automatic Code Scaffolding: On Model Create
    * Creates Model class
    * Creates Field classes
    * Creates Crud++ Controller
* API
    * REST Compliant
    * Generates HTML5 + AngularJS forms that provide client and server-side validation