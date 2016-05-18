<?php
namespace Craft;

return [
  'endpoints' => [
    'events.json' => [
      'elementType' => 'Entry',
      'criteria' => ['section' => 'events'],
      'transformer' => function(EntryModel $entry) {
        $photos = [];
        foreach ($entry->photo as $p) {
          $photos[] = $p->url;
        }

        return [
          'title' => $entry->title,
          'photos' => $photos,
          'starts' => $entry->startDateTime,
          'track' => $entry->track,
          'latitude' => $entry->latitude,
          'longitude' => $entry->longitude,
          'url' => $entry->url,
          'jsonUrl' => UrlHelper::getUrl("events/{$entry->id}.json"),
        ];
      },
    ],
    'events/<entryId:\d+>.json' => function($entryId) {

     return [
        'elementType' => 'Entry',
        'criteria' => ['id' => $entryId],
        'first' => true,
        'transformer' => function(EntryModel $entry) {
          $photos = [];
          foreach ($entry->photo as $p) {
            $photos[] = $p->url;
          }

          return [
            'title' => $entry->title,
            'url' => $entry->url,
            'photos' => $photos,
            'starts' => $entry->startDateTime,
            'startingAddress' => $entry->startingAddress,
            'startingLocation' => $entry->startingLocation,
            'latitude' => $entry->latitude,
            'longitude' => $entry->longitude,
            'track' => $entry->track,
            'difficulty' => $entry->rideDifficulty,
            'description' => ($entry->description ? (string) $entry->description : false),
            'facebookId' => $entry->facebookId,
          ];
        },
      ];
    },
  ]
];
