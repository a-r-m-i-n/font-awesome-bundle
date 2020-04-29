# FontAwesome - Symfony Bundle

This bundle for Symfony Framework, allows you to add [FontAwesome](https://fontawesome.com/) SVG icons,
inline in your html.

It is released under MIT license.


## Installation

To install this package, you can just use composer:

```
$ composer require armin/font-awesome-bundle
```

This will also require the [fortawesome/font-awesome](https://packagist.org/packages/fortawesome/font-awesome) package.

Please make sure, you've registred the bundle correctly in your project's `config/bundles.php`.
There is no configuration to be made.


## Features

- Embed FontAwesome svg icons in our html output
- Without need of any CSS or JavaScript include
- Usage of SVG sprites:
    - When the same icon is used several times on the same page (e.g. arrow icons), 
      every additional instance of this icon will point to the first occurence in html output
    - Each instance can have individual options, like size or color
    - This saves space, in html output
- No need to copy/symlink SVG assets from `vendor/` to `public/`    


## Usage

Once this bundle is installed, you can use the following Twig function:

```
 {{ fa("smile-beam") }} == {{ fa("fas smile-beam") }}
 {{ fa("far smile-beam") }}
 {{ fa("far smile-beam", {size: 256, color: '#d50', class: 'card shadow'}) }}
```

It is recommended, to add some default CSS. 
All icons in html output, will have got the class `fa-svg-icon` set:
```
.fa-svg-icon {
  width: 32px;
  height: 32px;
  fill: #444;
}
```
When you provide options, like size or color, inline styles will overwrite the default CSS.


## Support

If you like this Symfony bundle, you are invited to [donate some funds](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2DCCULSKFRZFU)
to support further development. Thank you!

For help please visit the issue section on Github.
