SegmentationTreeBundle
======================

Allow to organize items in hierarchical segments  (Replace ClassificationTree)

Install
=======

To install for dev:

```bash
$ php composer.phar update --dev
```

To use as dependecy, use composer and add bundle in your AppKernel :

```yaml
    "require": {
        [...]
        "oro/SegmentationTreeBundle": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:laboro/SegmentationTreeBundle.git",
            "branch": "master"
        }
    ]
```


Classes / Concepts
==================

(dependencies : just doctrine entities but manager is agnostic)



Example of usage
================

Define segmentation tree manager as service
-------------------------------------------
In your services.yml file, define your service like this :
```yaml
services:
    segmentation_tree_manager:
        class:     %oro_segmentation_tree.segment_manager.class%
        arguments: [@doctrine.orm.entity_manager, %segment_class%]
```


Implement segmentation tree with simple doctrine entity
-------------------------------------------------------


Translate segments
------------------


Use segmentation tree with flexible entity
------------------------------------------

