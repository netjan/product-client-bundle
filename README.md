## Installation

composer require netjan/product-client-bundle

## Configuration

File `.env.local`
```
NETJAN_API_SERVER="http://127.0.0.1:8000/api/"
```

File: `config/routes.yaml`

```
netjan_product:
    prefix: '/client'
    resource: "@NetJanProductClientBundle/config/routing.yaml"
```

File: `config/packages/eight_points_guzzle.yaml`
```
eight_points_guzzle:
    clients:
        netjan_product:
            base_url: '%env(NETJAN_API_SERVER)%'
            options:
                headers:
                    Accept: 'application/json'
```


## Start

```
php -S 127.0.0.1:3000 -t public
```

## Usage

```
http://127.0.0.1:3000/client/product
```
