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

(dependencies : just doctrine entities)



Example of usage
================


Implement segmentation tree with simple doctrine entity
-------------------------------------------------------


Translate segments
------------------


Use segmentation tree with flexible entity
------------------------------------------

