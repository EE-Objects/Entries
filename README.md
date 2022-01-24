# EE Objects Entries

This library allows developers to treat ExpressionEngine Channel Entries as objects within their Addons. 

### The Problems This Solve

There are two main points this library covers; canonical keys and data types. With the first party Member Model within ExpressionEngine, you're dealing with mostly raw data delivered within a raw format. Specifically, custom fields are delivered in their raw database key and the raw value. 

This can complicate development so this library removes that concern. 

## Requirements
- ExpressionEngine >= 5.5
- PHP >= 7.1
 
## Installation

Add `ee-objects/entries` as a requirement to your `composer.json`:

```bash
$ composer require ee-objects/entries
```

### Implementation

```php
use EeObjects\Channels\Entries\Entry;

$entry = ee('ee_objects:ChannelEntryService')->getEntry(5079);
if ($entry instanceof Entry) {
    $my_custom_filed = $entry->get('my_custom_filed');

    $entry->set('my_custom_filed', 'Some Value');
    $entry->save();

    $entry->delete();
}
```

## Docs

Available in the [Wiki](https://github.com/EE-Objects/Entries/wiki "Wiki") and the [EeObjects Addon](https://github.com/EE-Objects/Example-Addon) repository