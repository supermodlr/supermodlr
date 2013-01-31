# Getting Started

## Requirements
* PHP 5.4
* MongoDB (For Supermodlr-ui development only meta-data)

## Modules
* Supermodlr
    * This is the core orm, api, and form generator
* Supermodlr-ui
    * This is meant to be a **development only** module that gives a developer an interface for creating models and fields.
* Shmvc
    * This is the template/theme module used for all view selection logic

## Config
* See /supermodlr-ui/config/supermodlr.php for initial database configuration for the Supermodlr-ui interface

## Setup
* Pull the Supermodlr-app repo
* Ensure your local mongo is setup or copy the /supermodlr-ui/config/supermodlr.php into your application/config directory and add valid mongodb connection information
* Go to /supermodlr-ui and start adding models and fields!