<?php
/**
 *
 * Kirby plugin for setting created dates
 *
 * @version   1.0.0
 * @link      https://github.com/jongacnik/kirby-createddate
 * @license   MIT <http://opensource.org/licenses/MIT>
 */

class CreatedDatePlugin {
    /**
     * @var string
     */
    protected $fieldName;

    public function __construct($fieldName) {
        $this->fieldName = $fieldName;
    }

    public function onPageCreate($page) {
        if ($this->fieldExists($page)) {
            $page->update(array(
                $this->fieldName => $this->getCurrentTime()
            ));
        }
    }

    public function onPageUpdate($page) {
        if (
            $this->fieldExists($page) &&
            $page->{$this->fieldName}()->isEmpty()
        ) {
            $page->update(array(
                $this->fieldName => (string) $page->modified()
            ));
        }
    }

    /**
     * @return string
     */
    protected function getCurrentTime() {
        return (string) time();
    }

    /**
     * @return bool
     */
    protected function fieldExists($page) {
        // KIRBY is missing an $page->field()->exists() method
        $contentArray = $page->content()->toArray();

        // Check if field isset
        return isset($contentArray[$this->fieldName]);
    }
}

// Allow to overwrite field name in config.php with c::set('createddate.name', 'NameMyField');
$fieldName = c::get('createddate.name', 'createddate');

// Create an instance and hook into kirby
$plugin = new CreatedDatePlugin($fieldName);

// Set date for new pages
kirby()->hook('panel.page.create', function($page) use ($plugin) {
    return $plugin->onPageCreate($page);
});

// Set date for existing pages (if added later)
kirby()->hook('panel.page.update', function($page) use ($plugin) {
    return $plugin->onPageUpdate($page);
});
