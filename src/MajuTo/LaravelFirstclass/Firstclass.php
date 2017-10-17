<?php

namespace MajuTo\LaravelFirstclass;

class Firstclass
{
    const CONFIG_FILE_NAME = 'firstclass';

    protected $ifGroups;
    protected $ifLists;
    protected $newUsers;
    protected $updatedUsers;
    protected $deletedUser;
    protected $usersAddedToGroups;
    protected $usersAddedToLists;
    protected $listsToClean;
    protected $reply;
    protected $subject;

    /**
     * Firstclass constructor.
     */
    public function __construct()
    {
        $this->subject = 'Batch admin';
        $this->reply = false;
        $this->init();
    }

    /**
     * Initialise les champs
     *
     * @return Firstclass
     */
    public function init()
    {
        $this->ifGroups = '';
        $this->ifLists = '';
        $this->newUsers = '';
        $this->updatedUsers = '';
        $this->usersAddedToGroups = '';
        $this->usersAddedToLists = '';
        $this->listsToClean = '';

        return $this;
    }

    /**
     * @param string $uid Uid
     * @param string $firstname
     * @param string $lastname
     * @param string $password
     * @param string $group
     *
     * @return FirstClass
     */
    public function addUSer($uid, $firstname, $lastname, $password, $group = 'Personnels')
    {
        $this->newUsers .= "ADD NETWORK " . $uid . " " . $firstname . " \"\" " . $lastname . " \"\" " . $password . " \"\" \"\" \"\" 1 " . $group;

        return $this;
    }

    /**
     * @param string $firstname Prénom
     * @param string $lastname Nom de famille
     * @param string $list_name Nom de la liste sous format "list-nom_liste"
     * @return FirstClass
     * @internal param string $user_name Nom de l'utilisateur sous format "Prénom NOM"
     */
    public function addUserToList($firstname, $lastname, $list_name)
    {
        $user_name = str_replace(' ', '-', $firstname) . ' ' . $lastname;
        if (!is_string( $list_name ))
        {
            die('ERREUR : Le nom de la liste doit être une chaîne de caractères');
        }

        if (substr($list_name, 0, 4) != "list")
        {
            die('ERREUR : Le nom de la liste doit commencer par "list"');
        }

        $this->usersAddedToLists .= "PUT \"Mail Lists:" . $list_name . "\" 4 0 \"" . $user_name . "\" +c\n";

        return $this;
    }

    /**
     * @param String $user_id
     * @param String $group_name
     * @return Firstclass
     * @throws \Exception
     */
    public function addUserToGroup ($user_id, $group_name)
    {
        if ( !is_string( $group_name ) )
        {
            throw new \Exception('ERREUR : Le nom du groupe doit être une chaîne de caractères');
        }

        $this->usersAddedToGroups .= "PGADD " . $user_id . " " . $group_name . " +c\n";

        return $this;
    }

    /**
     * Supprime tous les utilisateurs de la liste
     * de diffusion passée en parametre
     * @param $listName
     * @return Firstclass
     */
    public function cleanList($listName)
    {
        $this->listsToClean .= "DELFLD path \"Mail Lists:$listName\" 4 -1\n";
        return $this;
    }

    /**
     * @return Firstclass
     */
    public function reply ()
    {
        $this->reply = true;

        return $this;
    }

    /**
     * @param $group string Nom du groupe
     *
     * @return Firstclass
     */
    public function ifGroupMissing ($group)
    {
        $this->ifGroups .= "IF OBJECT \"Groups:" . $group . "\" MISSING\n";
        $this->ifGroups .= "NEW \"Groups\"\"" . $group . "\" \"\" FormDoc 23003 -1 -1 124 124 -U\n";
        $this->ifGroups .= "Put Previous 1272.0 7 602\n";
        $this->ifGroups .= "ENDIF\n\n";

        return $this;

    }

    /**
     * @param $list string Nom de la liste
     *
     * @return Firstclass
     */
    public function ifListMissing ($list)
    {
        $this->ifLists .= "IF OBJECT \"Mail Lists:" . $list . "\" MISSING\n";
        $this->ifLists .= "NEW \"Mail Lists\"\"" . $list . "\" \"\" FormDoc 23005 -1 -1 118 118 +P -U\n";
        $this->ifLists .= "PGADD \"" . $list . " Administratif\n";
        $this->ifLists .= "ENDIF\n\n";

        return $this;

    }

    /**
     * @param $uid
     * @param string | null $firstname
     * @param string | null $lastname
     * @param string | null $password
     * @param int $remote
     * @return Firstclass
     */
    public function updateUser ($uid, $firstname = null, $lastname = null, $password = null, $remote = 0)
    {
        $row = '';
        if ($firstname != null)
        {
            $row .= ' 1202 0 ' . str_replace(' ', '-', $firstname);
        }

        if ($lastname != null)
        {
            $row .= ' 1204 0 ' . $lastname;
        }

        if ($password != null)
        {
            $row .= ' 1217 0 ' . $password;
        }

        if ($remote)
        {
            $row .= ' 1223 7 5';
        }
        else
        {
            $row .= ' 1223 7 1';
        }

        if ( $row != '' )
        {
            $row = 'PUT USER ' . $uid . $row . "\n";
        }

        $this->updatedUsers .= $row;

        return $this;

    }

    /**
     * @param $uid
     * @return Firstclass
     */
    public function deleteUser ($uid)
    {
        $row = 'DEL ' . $uid . "\n";

        $this->updatedUsers .= $row;

        return $this;
    }

    /**
     * @param $uid
     * @param $aliases
     * @return Firstclass
     */
    public function setAliases ($uid, $aliases)
    {
        if (is_array($aliases))
        {
            $aliases = implode(',', $aliases);
        }

        if (! is_string($aliases))
        {
            die('ERREUR : Les alias doivent êtres fournis en chaîne de caractères');
        }

        $this->updatedUsers .= "PUT USER " . $uid . " 1252 0 " . $aliases . "\n";

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return Firstclass
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function makeBody ()
    {
        $body = '';

        if ( $this->reply && ($this->ifGroups || $this->ifLists || $this->newUsers || $this->updatedUsers || $this->usersAddedToGroups || $this->usersAddedToLists || $this->listsToClean) )
        {
            $body .= "REPLY\n";
        }
        $body .= ($this->listsToClean) ? $this->listsToClean . "\n" : '';
        $body .= ($this->ifGroups) ? $this->ifGroups . "\n" : '';
        $body .= ($this->ifLists) ? $this->ifLists . "\n" : '';
        $body .= ($this->newUsers) ? $this->newUsers . "\n" : '';
        $body .= ($this->updatedUsers) ? $this->updatedUsers . "\n" : '';
        $body .= ($this->usersAddedToGroups) ? $this->usersAddedToGroups . "\n" : '';
        $body .= ($this->usersAddedToLists) ? $this->usersAddedToLists . "\n" : '';

        return $body;
    }
}