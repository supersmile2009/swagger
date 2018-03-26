<?php

namespace Draw\Swagger;

use Draw\Swagger\Schema\SpecificationExtensionSupportInterface;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;

class JMSSerializerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ['event' => Events::PRE_SERIALIZE, 'method' => 'onPreSerialize'],
            ['event' => Events::PRE_DESERIALIZE, 'method' => 'onPreDeserialize'],
            ['event' => Events::POST_SERIALIZE, 'method' => 'onPostSerialize'],
        ];
    }

    /**
     * @param PreSerializeEvent $event
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $object = $event->getObject();
        if (is_object($object) &&
            is_subclass_of($object, 'Draw\Swagger\Schema\BaseParameter') &&
            get_class($object) !== $event->getType()['name']
        ) {
            $event->setType(get_class($event->getObject()));
        }
    }

    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $data = $event->getData();

        $type = $event->getType();

        if (!class_exists($type['name'])) {
            return;
        }

        if (!is_array($data)) {
            return;
        }

        $vendorData = [];

        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (strpos($key, 'x-') !== 0) {
                continue;
            }

            unset($data[$key]);
            $vendorData[$key] = $value;
        }

        if (!$vendorData) {
            return;
        }

        $reflectionClass = new \ReflectionClass($type['name']);
        if (!$reflectionClass->implementsInterface('Draw\Swagger\Schema\SpecificationExtensionSupportInterface')) {
            return;
        }

        $data['vendor'] = $vendorData;
        $event->setData($data);
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        $object = $event->getObject();

        /* @var $visitor \JMS\Serializer\JsonSerializationVisitor */
        $visitor = $event->getVisitor();

        if ($object instanceof SpecificationExtensionSupportInterface) {
            foreach ($object->getCustomProperties() as $key => $value) {
                if ($value !== null) {
                    $visitor->addData("x-{$key}", $value);
                }
            }
        }
    }
}
