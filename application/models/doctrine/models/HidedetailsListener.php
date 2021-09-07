<?php

	class HidedetailsListener implements Doctrine_EventListener_Interface {

		public function preTransactionCommit(Doctrine_Event $event)
		{
			
		}

		public function postTransactionCommit(Doctrine_Event $event)
		{
			
		}

		public function preTransactionRollback(Doctrine_Event $event)
		{
			
		}

		public function postTransactionRollback(Doctrine_Event $event)
		{
			
		}

		public function preTransactionBegin(Doctrine_Event $event)
		{
			
		}

		public function postTransactionBegin(Doctrine_Event $event)
		{
			
		}

		public function postConnect(Doctrine_Event $event)
		{
			
		}

		public function preConnect(Doctrine_Event $event)
		{
			
		}

		public function preQuery(Doctrine_Event $event)
		{
			
		}

		public function postQuery(Doctrine_Event $event)
		{
			
		}

		public function prePrepare(Doctrine_Event $event)
		{
			
		}

		public function postPrepare(Doctrine_Event $event)
		{
			
		}

		public function preExec(Doctrine_Event $event)
		{
			$invoker = $event->getInvoker();
			echo $event->getinvoker()->first_name;
		}

		public function postExec(Doctrine_Event $event)
		{
			$invoker = $event->getInvoker();
			echo $event->getinvoker()->first_name;
		}

		public function preError(Doctrine_Event $event)
		{
			
		}

		public function postError(Doctrine_Event $event)
		{
			
		}

		public function preFetch(Doctrine_Event $event)
		{
			$invoker = $event->getInvoker();
			echo $event->getinvoker()->first_name;
		}

		public function postFetch(Doctrine_Event $event)
		{
			echo "hi";
			$invoker = $event->getInvoker();
			$first_name = $event->getinvoker()->first_name;
			echo $event->getCode();
		}

		public function preFetchAll(Doctrine_Event $event)
		{
			
		}

		public function postFetchAll(Doctrine_Event $event)
		{
			
		}

		public function preStmtExecute(Doctrine_Event $event)
		{
			$invoker = $event->getInvoker();
			echo $event->getinvoker()->first_name;
		}

		public function postStmtExecute(Doctrine_Event $event)
		{
			$invoker = $event->getInvoker();
			print_r($event->getinvoker());
			echo $event->getinvoker()->first_name;
			echo $event->getCode();
		}

	}

?>