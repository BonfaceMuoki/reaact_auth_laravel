
import React,{useState} from 'react'
import { DataGrid } from '@mui/x-data-grid'
import { Box, Button, ButtonBase, TextField, Grid, Divider } from '@mui/material';
import Typography from '@mui/material/Typography';
import Modal from '@mui/material/Modal';
import FlexBetween from 'components/FlexBetween';
import Header from 'components/Header';
import { Add } from '@mui/icons-material';
import ValuationFirmActions from './ValuationFirmActions';
import { useMediaQuery } from '@mui/material';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as yup from "yup";
import  "../../assets/scss/validation.css"

const style = {
    position: 'absolute',
    top: '50%',
    left: '50%',
    transform: 'translate(-50%, -50%)',
    width: '50%',
    bgcolor: 'background.paper',
    boxShadow: 24,
    p: 4,
};

function ValuationFirms() {
    const isNonMediumScreens = useMediaQuery("(min-width: 1200px)");
    ///intialize invite form
    const inviteformschema = yup.object().shape({
      valuation_firm_name: yup.string().required("Please provide the valuation firm registred name"),
      valuation_firm_admin_email: yup.string().email("Plase provide valid Email").required("Please provide email"),
      valuation_firm_isk_number: yup.string().required("Please provide the ISK number"),
      valuation_firm_director_name: yup.string().required("Please provide the director name")
    });
    const {
        register:registerInvitForm,
        handleSubmit:handleInviteFormsubmit,
        formState:{ errors: inviteFormErrors}} = useForm({
        resolver:yupResolver(inviteformschema)
    });
    ///intialize invite form
    //intialize edit form
    const editFromSchema= yup.object().shape({

    });
    const {
        register:editValuationFirmForm,
        handleSubmit:handleEditFormSubmit, 
        formState:{errors:editValuationFormErrors},
        control:editFormControl}=useForm();
    ///intialize edit form

    const columns = [
        {
            headerName: "ROLE ID",
            field: "id",
            flex: 1
        },
        {
            headerName: "ROLE NAME",
            field: "name",
            flex: 1
        },
        {
            field: 'actions',
            headerName: "Actions",
            type: 'actions',
            width: 400,
            renderCell: (row) =>{
                return (<div>
                <Button variant='contained' onClick={handleOpenValauationFirmModal}>Edit</Button>  &nbsp;
                <Button variant='contained' onClick={handleOpenValauationFirmModal}>Delete</Button>                  
              </div>);
            }
            
          ,
        }
    ];
    const roles = [
        {
            name: "Add permission",
            id: 1
        }, {
            name: "Add Role",
            id: 2
        },
    ];
    const [open, setOpen] = React.useState(false);
    const handleOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);

    const [openEditValauationFirmModal, setOpenEditValauationFirmModal] = useState(false);
    const handleOpenValauationFirmModal = () => setOpenEditValauationFirmModal(true);
    const handleCloseValauationFirmModal = () => setOpenEditValauationFirmModal(false);

    const onSubmitInviteFormsubmit = (data) => {
     console.log(data);
    }
    return (

        <>
              {/* close modal edit valuation firm */}
      <Modal
        open={openEditValauationFirmModal}
        onClose={handleCloseValauationFirmModal}
        aria-labelledby="modal-modal-title"
        aria-describedby="modal-modal-description"
      >
        <Box sx={style}>
          <Typography>Edit Valauation Firm</Typography>
          <Divider></Divider>
          <Grid container spacing={2}  sx={{mt:1}} >
            <Grid item xs={12} sm={6} md={4}>
              <Typography>Valuation Firm Name</Typography>
              <TextField fullWidth />
            </Grid>
          </Grid>
        </Box>
      </Modal>
      {/* close modal edit valuation firm  */}

            <FlexBetween sx={{ ml: 5 }}>
                <Header sx={{ ml: 30 }} title="Valuation Firms" subtitle="List of Valuation Firms" />
                <Button sx={{ mt: 10, ml: 10, mr: 10 }} variant='contained' onClick={handleOpen}> <Add></Add>&nbsp;&nbsp; New Role</Button>
            </FlexBetween>
            {/* modal invite valuation firm */}
            <Modal
                open={open}
                onClose={handleClose}
                aria-labelledby="modal-modal-title"
                aria-describedby="modal-modal-description"
            >
                <Box sx={style}>
                    <Typography id="modal-modal-title" variant="h6" component="h2">
                        INVITE VALUATION FIRM
                    </Typography>
                    <hr></hr>
                    <form name='invitevaluationformform' onSubmit={handleInviteFormsubmit(onSubmitInviteFormsubmit())}>
                    <Grid container spacing={2} sx={{mt:2, ml:2, width:"100%"}}>
                        
                    <Grid item xs={12} sm={6} md={6} >
                            <Typography>Valuation Firm</Typography>
                            <div>
                                <TextField autoComplete="off" fullWidth {...registerInvitForm("valuation_firm_name")} />
                                <span className='errorSpan' >{inviteFormErrors.valuation_firm_name?.message}</span>
                            </div>
                        </Grid>
                        <Grid item xs={12} sm={6} md={6} >
                            <Typography>Admin Email</Typography>
                            <div>
                                <TextField autoComplete="off" fullWidth {...registerInvitForm("valuation_firm_isk_number")} />
                                <span className='errorSpan' >{inviteFormErrors.valuation_firm_isk_number?.message}</span>
                            </div>
                        </Grid>
                        <Grid item xs={12} sm={6} md={6} >
                            <Typography>ISK Number</Typography>
                            <div>
                                <TextField autoComplete="off" fullWidth {...registerInvitForm("valuation_firm_isk_number")} />
                                <span className='errorSpan' >{inviteFormErrors.valuation_firm_isk_number?.message}</span>
                            </div>
                        </Grid>
                        <Grid item xs={12} sm={6} md={6} >
                            <Typography>Director Name</Typography>
                            <div>
                                <TextField autoComplete="off" fullWidth {...registerInvitForm("valuation_firm_director_name")} />
                                <span className='errorSpan' >{inviteFormErrors.valuation_firm_director_name?.message}</span>
                            </div>
                        </Grid>
                        <Grid item xs={12} sm={12} md={12} >
                            <Button variant='contained' fullWidth type="submit">Send Invite</Button>
                        </Grid>
                      
                    </Grid>
                    </form>
                </Box>
            </Modal>
            {/* modal invite valuation firm */}
 
            <Box sx={{ mt: 10, ml: 10, mr: 10, height: "450px;" }} >
                <DataGrid
                    columns={columns}
                    rows={roles}
                    getRowId={(row) => row.id}

                />
            </Box>
        </>

    )
}

export default ValuationFirms