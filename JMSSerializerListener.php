<?php

namespace Draw\Swagger;

use Draw\Swagger\Schema\BaseParameter;
use Draw\Swagger\Schema\SpecificationExtensionSupportInterface;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;

class JMSSerializerListener implements EventSubscriberInterface
{
    /**
     * @return array
     */
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
    public function onPreSerialize(PreSerializeEvent $event): void
    {
        $object = $event->getObject();
        if (\is_object($object) &&
            \is_subclass_of($object, BaseParameter::class) &&
            \get_class($object) !== $event->getType()['name']
        ) {
            $event->setType(\get_class($event->getObject()));
        }
    }

    /**
     * @param PreDeserializeEvent $event
     *
     * @throws \ReflectionException
     */
    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        $data = $event->getData();

        $type = $event->getType();

        if (!\class_exists($type['name'])) {
            return;
        }

        $reflectionClass = new \ReflectionClass($type['name']);
        if (!$reflectionClass->implementsInterface(SpecificationExtensionSupportInterface::class)) {
            return;
        }

        if (!\is_array($data)) {
            return;
        }

        $customProperties = [];

        foreach ($data as $key => $value) {
            if (!\is_string($key)) {
                continue;
            }

            if (strpos($key, 'x-') !== 0) {
                continue;
            }

            unset($data[$key]);
            $customProperties[$key] = $value;
        }

        if (!$customProperties) {
            return;
        }

        $data['customProperties'] = $customProperties;
        $event->setData($data);
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        $object = $event->getObject();

        /* @var $visitor \JMS\Serializer\JsonSerializationVisitor */
        $visitor = $event->getVisitor();

        if ($object instanceof SpecificationExtensionSupportInterface) {
            foreach ($object->getCustomProperties() as $key => $value) {
                if ($value !== null) {
                    $visitor->setData("x-{$key}", $value);
                }
            }
        }
    }
}
