# TemplatePHP EE extension

## What is it?

This extension is designed for staged deployments with PHP in some templates.  Its goal is to keep you from interacting with the template manager to enable PHP parsing for particular templates.

It hooks the template_fetch_template call, and a quirk is that you'll need to reload a particular page twice for a configuration change to take effect since that hook doesn't allow pre-parsing modificiation to the template settings.

## How to use it

1. Install the extension in system/expressionengine/third_party/templatephp
2. Add the `config['template_php']` item to your config.php to specify which template settings to modify.  The item should contain one or more subarrays: 'input' (early PHP processing), 'output' (late PHP processing), and 'disable' (no PHP processing).  Templates should be specified as 'group/name'.  For example:

```php
$config['template_php'] = array(
  'input' => array('events/view',
                    'helpers/get-events',
                    'helpers/get-mini-calendar',
                    'helpers/_get-video',
                    'helpers/_get-soundcloud',
                    'helpers/get-news',
                    'helpers/get-blog',
                    'helpers/get-events-featured'),
  'output' => array('site/index',
                    'site/redirect',
                    'helpers/date-calc'),
  'disable' => array()
);
```

## Performance considerations

This extension adds 2 SQL queries to any given page load.  I'd prefer to hook something like a cache flush on the control panel side, but EE hooks are pretty limited.