<?php

namespace Icinga\Module\Director\Objects;

use Icinga\Module\Director\IcingaConfig\IcingaConfigHelper as c;
use RuntimeException;

class IcingaNotification extends IcingaObject
{
    protected $table = 'icinga_notification';

    protected $defaultProperties = [
        'id'                    => null,
        'object_name'           => null,
        'object_type'           => null,
        'disabled'              => 'n',
        'apply_to'              => null,
        'host_id'               => null,
        'service_id'            => null,
        // 'users'                 => null,
        // 'user_groups'           => null,
        'times_begin'           => null,
        'times_end'             => null,
        'command_id'            => null,
        'notification_interval' => null,
        'period_id'             => null,
        'zone_id'               => null,
        'assign_filter'         => null,
    ];

    protected $supportsCustomVars = true;

    protected $supportsFields = true;

    protected $supportsImports = true;

    protected $supportsApplyRules = true;

    protected $relatedSets = [
        'states' => 'StateFilterSet',
        'types'  => 'TypeFilterSet',
    ];

    protected $multiRelations = [
        'users'       => 'IcingaUser',
        'user_groups' => 'IcingaUserGroup',
    ];

    protected $relations = [
        'zone'    => 'IcingaZone',
        'host'    => 'IcingaHost',
        'service' => 'IcingaService',
        'command' => 'IcingaCommand',
        'period'  => 'IcingaTimePeriod',
    ];

    protected $intervalProperties = [
        'notification_interval' => 'interval',
        'times_begin'           => 'times_begin',
        'times_end'             => 'times_end',
    ];

    protected function prefersGlobalZone()
    {
        return false;
    }

    /**
     * We have distinct properties in the db
     *
     * ...but render times only once
     *
     * And we skip warnings about underscores in method names:
     * @codingStandardsIgnoreStart
     *
     * @return string
     */
    protected function renderTimes_begin()
    {
        // @codingStandardsIgnoreEnd
        $times = (object) [
            'begin' => c::renderInterval($this->times_begin)
        ];

        if ($this->get('times_end') !== null) {
            $times->end = c::renderInterval($this->get('times_end'));
        }

        return c::renderKeyValue('times', c::renderDictionary($times));
    }

    /**
     * We have distinct properties in the db
     *
     * ...but render times only once
     *
     * And we skip warnings about underscores in method names:
     * @codingStandardsIgnoreStart
     *
     * @return string
     */
    protected function renderTimes_end()
    {
        // @codingStandardsIgnoreEnd

        if ($this->get('times_begin') !== null) {
            return '';
        }

        $times = (object) [
            'end' => c::renderInterval($this->get('times_end'))
        ];

        return c::renderKeyValue('times', c::renderDictionary($times));
    }

    /**
     * Do not render internal property apply_to
     *
     * Avoid complaints for method names with underscore:
     * @codingStandardsIgnoreStart
     *
     * @return string
     */
    public function renderApply_to()
    {
        // @codingStandardsIgnoreEnd
        return '';
    }

    protected function renderObjectHeader()
    {
        if ($this->isApplyRule()) {
            if (($to = $this->get('apply_to')) === null) {
                throw new RuntimeException(sprintf(
                    'Applied notification "%s" has no valid object type',
                    $this->getObjectName()
                ));
            }

            return sprintf(
                "%s %s %s to %s {\n",
                $this->getObjectTypeName(),
                $this->getType(),
                c::renderString($this->getObjectName()),
                ucfirst($to)
            );
        } else {
            return parent::renderObjectHeader();
        }
    }

    protected function setKey($key)
    {
        if (is_int($key)) {
            $this->id = $key;
        } elseif (is_array($key)) {
            foreach (['id', 'host_id', 'service_id', 'object_name'] as $k) {
                if (array_key_exists($k, $key)) {
                    $this->set($k, $key[$k]);
                }
            }
        } else {
            return parent::setKey($key);
        }

        return $this;
    }
}
