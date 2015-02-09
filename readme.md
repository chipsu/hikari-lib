# **FRAMEWORK**
Please excuse me, this is not a proper documentation yet.
If you're good/less terrible than me at documenting stuff and want to help, please let me know!

**TBD**

# Intro
This framework is a personal hobby project that has a goal to someday be usable by other people.

Some key features are:
- Developer friendly: Write less code and write code fast.
- High performance: HHVM-compatible, low memory and efficient caching.
- Keep it simple, stupid.
- Keep amount of 3rd-party dependencies as low as possible.

There are also a set of optional libraries:
- **FRAMEWORK**-js: A basic set of javascript widgets and ajax functionallity.
- **FRAMEWORK**-reset: A SASS-library that provides a sane reset and some basic styling.
- **FRAMEWORK**-app: Demo app.
- **FRAMEWORK**-cms: A mongo app and library that provides a full MongoDB-powered CMS-system with users, posts and more.

**FRAMEWORK** **very** much inspired by other frameworks like Yii2 & Symfony, but we try to do a few key functions differently:
- Views are not a central concept that all other Components know about, the controller knowns nothing of views or how they work. View rendering is just the result of a filter() and data output is treated like any other (JSON, XML).
- That's pretty much all I can think of right now :)

**NOTE** **FRAMEWORK** is far from complete!
So, if you're looking for an enterprise-ready framework, look elsewhere.
If you need a good PHP-framework, I highly recommend Yii2, seriously, it's awesome.

# Components
A components is the base class for all services in **FRAMEWORK**. The Component is a Dependency Injection container.
Its behaviour can be modified with events & mixins, either from the application config or at runtime.

**TBD**

## Configuration
**TBD**

## Properties
**TBD**

## Dependencies
**TBD**

## Mixins
**TBD**

### Mixins vs Traits
**TBD**

## Events
**TBD**

## Sharing
**TBD**


# Controllers
**TBD**


# Application
**TBD**

## Application lifecycle
- Install autoloader
- Create App & load config
- Parse request
- Pass request to controller
- Resolve controller-action
- Run beforeAction events & filters
  - filter\Header: Setup lang and stuff from http headers
  - filter\Response: Detect output format from http accept
  - Authentication
  - Authorization
- Run action & store result
- Run afterAction & apply filters to result
  - ResponseFormatter will render HTML, JSON, XML or something else
- Output result


# Models
**FRAMEWORK** provides a basic Active Record like set of components to fetch data from MongoDB and PDO-databases.
It's optional and can easially be replaced with a 3rd-party ORM or just plain PDO.

**TBD**


# Views
Views in **FRAMEWORK** are actually a controller filter that modifies the result from an action in the Controller::afterAction event.
The difference from other OutputFilters is that it uses view-templates to generate the content.
Templates can use plain PHP or a custom language by specifying a custom renderer.

**TBD**
