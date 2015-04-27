# NoGo Api Server

## Why?

NoGo Api server provide access via a json rest api to you database.

## Comes with

- Resource controller
- Resource factory
- Eloquent
- Json middleware and view

## Configuration

Add resource controller to routes:
```
routes:
    - "Nogo\\Api\\Controller\\Resource"
```

Define your api
```
api:
    prefix: "/api" # Api prefix
    version: 1     # Versioning
    resources:
        name:      # Resource name after /api
            model: "Namespace\\To\\Model"
```