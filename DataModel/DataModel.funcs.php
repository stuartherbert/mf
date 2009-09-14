<?php

// ========================================================================
//
// DataModel/DataModel.funcs.php
//              Functions defined by the DataModel component
//
//              Part of the Methodosity Framework for PHP appliations
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-07-28   SLH     Created
// 2008-08-11   SLH     Added constraint_mustBeValidModel()
// 2009-05-26   SLH     Added __mf_extend_model()
// 2009-06-01   SLH     Updated __mf_extend_model() to use new API
// 2009-09-15	SLH	Renamed from Model to DataModel
// ========================================================================

function constraint_modelMustBeCalled($oDef, $name)
{
	if ($oDef->getModelName() != $name)
        {
        	throw new DataModel_E_IncompatibleDefinition
                (
                        'model',
                        $oDef->getModelName(),
                        $name
                );
        }
}

function constraint_mustBeValidModel($model)
{
	$oDef = DataModel_Definitions::getIfExists($model);
}

function __mf_extend_model($modelName, $extensionClass)
{
        Obj_MixinsManager::extend($modelName)->withClass($extensionClass);
        Events_Manager::triggerEvent('modelExtended', $extensionClass, $modelName);
}

?>
