# Bookmarklet Gen [![](https://travis-ci.org/ozh/bookmarkletgen.svg?branch=master)](https://travis-ci.org/ozh/bookmarkletgen)

Convert readable Javascript code into bookmarklet links

## Destaques

- Remove comentários

- Compacta o código removendo espaços estranhos, mas não dentro de strings literais.
  
  Exemplo:
    ```javascript
  function   someName(   param   ) {
     alert( "this is a string" )
  }
    ```
  Irá Retornar:
    ```javascript
  function%20someName(param){alert("this%20is%20a%20string")}
    ```
- Encoda o que será necessário encodar.

- wraps code into a self invoking function ready for bookmarking

This is basically a slightly enhanced PHP port of the excellent Bookmarklet Crunchinator: 
http://ted.mielczarek.org/code/mozilla/bookmarklet.html

## Instalação

Se você estiver usando o Composer, adicione este requisito ao seu arquivo `composer.json` e execute `composer install`:

    {
        "require": {
            "ozh/phpass": "1.2.0"
        }
    }

Ou simplesmente na linha de comando: `composer install ozh/bookmarkletgen`

Se você não estiver usando o composer, baixe o arquivo de classe e inclua-o manualmente.

## Exemplo

```php
<?php
$javascript = <<<CODE
var link="http://google.com/"; // destination
window.location = link;
CODE;

require 'vendor/autoload.php'; // Se quiser instalar usando o Composer
require 'path/to/Bookmarkletgen.php'; // Por outro lado

$book = new \Ozh\Bookmarkletgen\Bookmarkletgen;
$link = $book->crunch( $javascript );

printf( '<a href="%s">bookmarklet</a>', $link );
```

Irá printar:

```html
<a href="javascript:(function()%7Bvar%20link%3D%22http%3A%2F%2Fgoogle.com%2F%22%3Bwindow.location%3Dlink%3B%7D)()%3B">bookmarklet</a>
```

## Testes

Esta biblioteca vem com testes de unidade para garantir que o Javascript processado resultante, seja um código válido.

Esta biblioteca requer pelo menos o PHP 5.3. Os testes estão falhando no HHVM por causa de um problema binário externo (`phantomjs`), mas as coisas devem funcionar de qualquer maneira no HHVM também. Vai na
fé.

## Licença

Eu criei, mas faça o que diabos você quer fazer com isso. 

