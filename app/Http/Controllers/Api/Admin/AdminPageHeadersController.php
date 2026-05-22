<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\PageHeadersController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPageHeadersController extends Controller
{
    use ConvertsAdminWebResponses;

    public function about(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editAbout());
    }

    public function updateAbout(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateAbout($request));
    }

    public function resetAbout(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetAbout());
    }

    public function appointment(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editAppointment());
    }

    public function updateAppointment(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateAppointment($request));
    }

    public function resetAppointment(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetAppointment());
    }

    public function contact(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editContact());
    }

    public function updateContact(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateContact($request));
    }

    public function resetContact(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetContact());
    }

    public function doctor(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editDoctor());
    }

    public function updateDoctor(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateDoctor($request));
    }

    public function resetDoctor(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetDoctor());
    }

    public function doctorDetails(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editDoctorDetails());
    }

    public function updateDoctorDetails(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateDoctorDetails($request));
    }

    public function resetDoctorDetails(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetDoctorDetails());
    }

    public function faq(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editFaq());
    }

    public function updateFaq(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateFaq($request));
    }

    public function resetFaq(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetFaq());
    }

    public function pricing(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editPricing());
    }

    public function updatePricing(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updatePricing($request));
    }

    public function resetPricing(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetPricing());
    }

    public function products(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editProducts());
    }

    public function updateProducts(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateProducts($request));
    }

    public function resetProducts(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetProducts());
    }

    public function productShow(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editProductShow());
    }

    public function updateProductShow(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateProductShow($request));
    }

    public function resetProductShow(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetProductShow());
    }

    public function services(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editServices());
    }

    public function updateServices(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateServices($request));
    }

    public function resetServices(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetServices());
    }

    public function serviceShow(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editServiceShow());
    }

    public function updateServiceShow(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateServiceShow($request));
    }

    public function resetServiceShow(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetServiceShow());
    }

    public function testimonials(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editTestimonials());
    }

    public function updateTestimonials(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateTestimonials($request));
    }

    public function resetTestimonials(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetTestimonials());
    }

    public function testimonialShow(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editTestimonialShow());
    }

    public function updateTestimonialShow(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateTestimonialShow($request));
    }

    public function resetTestimonialShow(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetTestimonialShow());
    }

    public function notFound(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editNotFound());
    }

    public function updateNotFound(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateNotFound($request));
    }

    public function resetNotFound(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetNotFound());
    }

    public function loginPage(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editLoginPage());
    }

    public function updateLoginPage(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateLoginPage($request));
    }

    public function resetLoginPage(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetLoginPage());
    }

    public function signUpPage(): JsonResponse
    {
        return $this->viewDataJson(app(PageHeadersController::class)->editSignUpPage());
    }

    public function updateSignUpPage(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->updateSignUpPage($request));
    }

    public function resetSignUpPage(): JsonResponse
    {
        return $this->adminWebJson(app(PageHeadersController::class)->resetSignUpPage());
    }
}
