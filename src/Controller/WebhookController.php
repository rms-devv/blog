<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\User;
use App\Entity\Plan;
use App\Entity\Subscription;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    #[Route('/webhook/stripe', name: 'app_webhook_stripe')]
    public function index(LoggerInterface $logger, ManagerRegistry $doctrine): Response
    {
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_sk'));
		$event = null;

		// Check request
		$endpoint_secret = $this->getParameter('stripe_webhook_secret');
		$payload = @file_get_contents('php://input');
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
		
		try {
		    $event = \Stripe\Webhook::constructEvent(
		        $payload, $sig_header, $endpoint_secret
		    );
		} catch(\UnexpectedValueException $e) {
			// Invalid payload
			$logger->info('Webhook Stripe Invalid payload');
		    http_response_code(400);
		    exit();
		} catch(\Stripe\Exception\SignatureVerificationException $e) {
			// Invalid signature
			$logger->info('Webhook Stripe Invalid signature');
		    http_response_code(403);
		    exit();
		}

		// Handle the event
		switch ($event->type) {
			case 'checkout.session.completed':
				$logger->info('Webhook Stripe connect checkout.session.completed');
				$session = $event->data->object;
				$subscriptionId = $session->subscription;

				$stripe = new \Stripe\StripeClient($this->getParameter('stripe_sk'));
				$subscriptionStripe = $stripe->subscriptions->retrieve($subscriptionId, array());
				$planId = $subscriptionStripe->plan->id;

				// Get user
				$customerEmail = $session->customer_details->email;
				$user = $doctrine->getRepository(User::class)->findOneByEmail($customerEmail);
				if (!$user) {
					$logger->info('Webhook Stripe user not found');
					http_response_code(404);
					exit();
				}

				// Disable old subscription
                dump($user->getId());
				$activeSub = $doctrine->getRepository(Subcription::class)->findActiveSub($user->getId());
				if ($activeSub) {
					\Stripe\Subscription::update(
						$activeSub->getStripeId(), [
							'cancel_at_period_end' => false,
						]
					);
					
					$activeSub->setIsActive(false);
					$doctrine->getManager()->persist($activeSub);
				}

				// Get plan
				$plan = $doctrine->getRepository(Plan::class)->findOneBy(['stripeId' => $planId]);
				if (!$plan) {
					$logger->info('Webhook Stripe plan not found');
					http_response_code(404);
					exit();
				}

				$subscription = new Subscription();
				$subscription->setPlan($plan);
				$subscription->setStripeId($subscriptionStripe->id);
				$subscription->setCurrentPeriodStart(new \Datetime(date('c', $subscriptionStripe->current_period_start)));
				$subscription->setCurrentPeriodEnd(new \Datetime(date('c', $subscriptionStripe->current_period_end)));
				$subscription->setUser($user);
				$subscription->setIsActive(true);
				$user->setStripeId($session->customer);

				$doctrine->getManager()->persist($subscription);
				$doctrine->getManager()->flush();
				break;
			case 'invoice.paid':
				$subscriptionId = $event->data->object->subscription;
				if (!$subscriptionId) {
					$logger->info('No subscription');
					break;
				}

				$subscription = null;
				for ($i = 0; $i <= 4 && $subscription === null; $i++) {
					$subscription = $doctrine->getRepository(Subscription::class)->findOneByStripeId($subscriptionId);
					if ($subscription) {
						break;
					}
					sleep(5);
				}

				$invoice = new Invoice();
				$invoice->setStripeId($event->data->object->id);
				$invoice->setSubscription($subscription);
				$invoice->setinvoiceNumber($event->data->object->number);
				$invoice->setamoundPaid($event->data->object->amount_paid);
				// Hosted invoice url is now generated by formator
				$invoice->setHostedInvoiceUrl($event->data->object->hosted_invoice_url);
				
				$doctrine->getManager()->persist($invoice);
				$doctrine->getManager()->flush();

				break;
		    default:
		        // Unexpected event type
		        http_response_code(400);
		        exit();
		}

		http_response_code(200);

		$response = new Response('success');
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
