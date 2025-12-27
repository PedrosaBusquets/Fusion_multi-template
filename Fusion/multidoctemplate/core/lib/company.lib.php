<?php
/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	Societe	$object		Object company shown
 * @return 	array				Array of tabs
 */
function cust_societe_prepare_head(CustomSociete $object)
{
    global $db, $langs, $conf, $user;
    $h = 0;
    $head = array();

    $head[$h][0] = DOL_URL_ROOT.'/societe/card.php?socid='.$object->id;
    $head[$h][1] = $langs->trans("Card");
    $head[$h][2] = 'card';
    $h++;

    if (empty($conf->global->MAIN_SUPPORT_SHARED_CONTACT_BETWEEN_THIRDPARTIES))
    {
	    if (empty($conf->global->MAIN_DISABLE_CONTACTS_TAB))
		{
		    //$nbContact = count($object->liste_contact(-1,'internal')) + count($object->liste_contact(-1,'external'));
			$nbContact = 0;	// TODO

			$sql = "SELECT COUNT(p.rowid) as nb";
			$sql .= " FROM ".MAIN_DB_PREFIX."socpeople as p";
			$sql .= " WHERE p.fk_soc = ".$object->id;
			$resql = $db->query($sql);
			if ($resql)
			{
				$obj = $db->fetch_object($resql);
				if ($obj) $nbContact = $obj->nb;
			}

		    $head[$h][0] = DOL_URL_ROOT.'/societe/contact.php?socid='.$object->id;
		    $head[$h][1] = $langs->trans('ContactsAddresses');
		    if ($nbContact > 0) $head[$h][1].= ' <span class="badge">'.$nbContact.'</span>';
		    $head[$h][2] = 'contact';
		    $h++;
		}
    }
    else
	{
		$head[$h][0] = DOL_URL_ROOT.'/societe/societecontact.php?socid='.$object->id;
		$nbContact = count($object->liste_contact(-1,'internal')) + count($object->liste_contact(-1,'external'));
		$head[$h][1] = $langs->trans("ContactsAddresses");
		if ($nbContact > 0) $head[$h][1].= ' <span class="badge">'.$nbContact.'</span>';
		$head[$h][2] = 'contact';
		$h++;
	}

    if ($object->client==1 || $object->client==2 || $object->client==3)
    {
        $head[$h][0] = DOL_URL_ROOT.'/comm/card.php?socid='.$object->id;
        $head[$h][1] = '';
        if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && ($object->client==2 || $object->client==3)) $head[$h][1] .= $langs->trans("Prospect");
        if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS) && $object->client==3) $head[$h][1] .= ' | ';
        if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS) && ($object->client==1 || $object->client==3)) $head[$h][1] .= $langs->trans("Customer");
        $head[$h][2] = 'customer';
        $h++;

        if (! empty($conf->global->PRODUIT_CUSTOMER_PRICES))
        {
            $langs->load("products");
            // price
            $head[$h][0] = DOL_URL_ROOT.'/societe/price.php?socid='.$object->id;
            $head[$h][1] = $langs->trans("CustomerPrices");
            $head[$h][2] = 'price';
            $h++;
        }
    }
    if (! empty($conf->fournisseur->enabled) && $object->fournisseur && ! empty($user->rights->fournisseur->lire))
    {
        $head[$h][0] = DOL_URL_ROOT.'/fourn/card.php?socid='.$object->id;
        $head[$h][1] = $langs->trans("Supplier");
        $head[$h][2] = 'supplier';
        $h++;
    }

    if (! empty($conf->projet->enabled) && (!empty($user->rights->projet->lire) ))
    {
    	$head[$h][0] = DOL_URL_ROOT.'/societe/project.php?socid='.$object->id;
    	$head[$h][1] = $langs->trans("Projects");
    	$nbNote = 0;
    	$sql = "SELECT COUNT(n.rowid) as nb";
    	$sql.= " FROM ".MAIN_DB_PREFIX."projet as n";
    	$sql.= " WHERE fk_soc = ".$object->id;
    	$sql.= " AND entity IN (".getEntity('project').")";
    	$resql=$db->query($sql);
    	if ($resql)
    	{
    		$num = $db->num_rows($resql);
    		$i = 0;
    		while ($i < $num)
    		{
    			$obj = $db->fetch_object($resql);
    			$nbNote=$obj->nb;
    			$i++;
    		}
    	}
    	else {
    		dol_print_error($db);
    	}
		if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
    	$head[$h][2] = 'project';
    	$h++;
    }

    // Tab to link resources
	if (! empty($conf->resource->enabled) && ! empty($conf->global->RESOURCE_ON_THIRDPARTIES))
	{
		$head[$h][0] = DOL_URL_ROOT.'/resource/element_resource.php?element=societe&element_id='.$object->id;
		$head[$h][1] = $langs->trans("Resources");
		$head[$h][2] = 'resources';
		$h++;
	}

	if (! empty($conf->global->ACCOUNTING_ENABLE_LETTERING))
	{
		// Tab to accountancy
		if (! empty($conf->accounting->enabled) && $object->client>0)
		{
			$head[$h][0] = DOL_URL_ROOT.'/accountancy/bookkeeping/thirdparty_lettering_customer.php?socid='.$object->id;
			$head[$h][1] = $langs->trans("TabLetteringCustomer");
			$head[$h][2] = 'lettering_customer';
			$h++;
		}

		// Tab to accountancy
		if (! empty($conf->accounting->enabled) && $object->fournisseur>0)
		{
			$head[$h][0] = DOL_URL_ROOT.'/accountancy/bookkeeping/thirdparty_lettering_supplier.php?socid='.$object->id;
			$head[$h][1] = $langs->trans("TabLetteringSupplier");
			$head[$h][2] = 'lettering_supplier';
			$h++;
		}
	}

	// Related items
    if (! empty($conf->commande->enabled) || ! empty($conf->propal->enabled) || ! empty($conf->facture->enabled) || ! empty($conf->ficheinter->enabled) || ! empty($conf->fournisseur->enabled))
    {
        $head[$h][0] = DOL_URL_ROOT.'/societe/consumption.php?socid='.$object->id;
        $head[$h][1] = $langs->trans("Referers");
        $head[$h][2] = 'consumption';
        $h++;
    }

    // Bank accounts
    if (empty($conf->global->SOCIETE_DISABLE_BANKACCOUNT))
    {
    	$nbBankAccount=0;
		$foundonexternalonlinesystem=0;
    	$langs->load("banks");

        $title = $langs->trans("BankAccounts");
		if (! empty($conf->stripe->enabled))
		{
			$langs->load("stripe");
			$title = $langs->trans("BankAccountsAndGateways");

			$servicestatus = 0;
			if (! empty($conf->global->STRIPE_LIVE) && ! GETPOST('forcesandbox','alpha')) $servicestatus = 1;

			include_once DOL_DOCUMENT_ROOT.'/societe/class/societeaccount.class.php';
			$societeaccount = new SocieteAccount($db);
			$stripecu = $societeaccount->getCustomerAccount($object->id, 'stripe', $servicestatus);		// Get thirdparty cu_...
			if ($stripecu) $foundonexternalonlinesystem++;
		}

        $sql = "SELECT COUNT(n.rowid) as nb";
        $sql.= " FROM ".MAIN_DB_PREFIX."societe_rib as n";
        $sql.= " WHERE fk_soc = ".$object->id;
        $resql=$db->query($sql);
        if ($resql)
        {
            $num = $db->num_rows($resql);
            $i = 0;
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
                $nbBankAccount=$obj->nb;
                $i++;
            }
        }
        else {
            dol_print_error($db);
        }

        //if (! empty($conf->stripe->enabled) && $nbBankAccount > 0) $nbBankAccount = '...';	// No way to know exact number

        $head[$h][0] = DOL_URL_ROOT .'/societe/paymentmodes.php?socid='.$object->id;
        $head[$h][1] = $title;
        if ($foundonexternalonlinesystem) $head[$h][1].= ' <span class="badge">...</span>';
       	elseif ($nbBankAccount > 0) $head[$h][1].= ' <span class="badge">'.$nbBankAccount.'</span>';
        $head[$h][2] = 'rib';
        $h++;
    }

    if (! empty($conf->website->enabled) && (! empty($conf->global->WEBSITE_USE_WEBSITE_ACCOUNTS)) && (!empty($user->rights->societe->lire)))
    {
    	$head[$h][0] = DOL_URL_ROOT.'/societe/website.php?id='.$object->id;
    	$head[$h][1] = $langs->trans("WebSiteAccounts");
    	$nbNote = 0;
    	$sql = "SELECT COUNT(n.rowid) as nb";
    	$sql.= " FROM ".MAIN_DB_PREFIX."societe_account as n";
    	$sql.= " WHERE fk_soc = ".$object->id.' AND fk_website > 0';
    	$resql=$db->query($sql);
    	if ($resql)
    	{
    		$num = $db->num_rows($resql);
    		$i = 0;
    		while ($i < $num)
    		{
    			$obj = $db->fetch_object($resql);
    			$nbNote=$obj->nb;
    			$i++;
    		}
    	}
    	else {
    		dol_print_error($db);
    	}
    	if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
    	$head[$h][2] = 'website';
    	$h++;
    }

	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'thirdparty');

    if ($user->societe_id == 0)
    {
        // Notifications
        if (! empty($conf->notification->enabled))
        {
        	$nbNote = 0;
        	$sql = "SELECT COUNT(n.rowid) as nb";
        	$sql.= " FROM ".MAIN_DB_PREFIX."notify_def as n";
        	$sql.= " WHERE fk_soc = ".$object->id;
        	$resql=$db->query($sql);
        	if ($resql)
        	{
        		$num = $db->num_rows($resql);
        		$i = 0;
        		while ($i < $num)
        		{
        			$obj = $db->fetch_object($resql);
        			$nbNote=$obj->nb;
        			$i++;
        		}
        	}
        	else {
        		dol_print_error($db);
        	}

        	$head[$h][0] = DOL_URL_ROOT.'/societe/notify/card.php?socid='.$object->id;
        	$head[$h][1] = $langs->trans("Notifications");
			if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
        	$head[$h][2] = 'notify';
        	$h++;
        }

		// Notes
        $nbNote = 0;
        if(!empty($object->note_private)) $nbNote++;
		if(!empty($object->note_public)) $nbNote++;
        $head[$h][0] = DOL_URL_ROOT.'/societe/note.php?id='.$object->id;
        $head[$h][1] = $langs->trans("Notes");
		if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
        $head[$h][2] = 'note';
        $h++;

        // Attached files
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
        $upload_dir = $conf->societe->multidir_output[$object->entity] . "/" . $object->id ;
        $nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview.*\.png)$'));
        $nbLinks=Link::count($db, $object->element, $object->id);

        $head[$h][0] = DOL_URL_ROOT.'/societe/document.php?socid='.$object->id;
        $head[$h][1] = $langs->trans("Documents");
		if (($nbFiles+$nbLinks) > 0) $head[$h][1].= ' <span class="badge">'.($nbFiles+$nbLinks).'</span>';
        $head[$h][2] = 'document';
        $h++;
    }

    $head[$h][0] = DOL_URL_ROOT.'/societe/agenda.php?socid='.$object->id;
    $head[$h][1].= $langs->trans("Events");
    if (! empty($conf->agenda->enabled) && (!empty($user->rights->agenda->myactions->read) || !empty($user->rights->agenda->allactions->read) ))
    {
        $head[$h][1].= '/';
        $head[$h][1].= $langs->trans("Agenda");
    }
    $head[$h][2] = 'agenda';
    $h++;

    // Log
    /*$head[$h][0] = DOL_URL_ROOT.'/societe/info.php?socid='.$object->id;
    $head[$h][1] = $langs->trans("Info");
    $head[$h][2] = 'info';
    $h++;*/

    complete_head_from_modules($conf,$langs,$object,$head,$h,'thirdparty','remove');

    return $head;
}