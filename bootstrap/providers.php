<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Masters\DocumentType\Providers\DocumentTypeServiceProvider::class,
    App\Modules\Masters\StudentTypes\Providers\StudentTypesServiceProvider::class,
    App\Modules\Core\Auth\Providers\AuthServiceProvider::class,
    App\Modules\Period\Providers\PeriodServiceProvider::class,
    App\Modules\Module\Providers\ModuleServiceProvider::class,
    App\Modules\Course\Providers\CourseServiceProvider::class,
    App\Modules\Enrollment\Providers\EnrollmentServiceProvider::class,
    App\Modules\Group\Providers\GroupServiceProvider::class,
];
