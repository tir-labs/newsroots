import { 
    __experimentalSpacer as Spacer,
    __experimentalHeading as Heading,
} from '@wordpress/components';


const InfoPanel = (props) => {
    return (
        <div style={{
            
            marginBottom : "1rem"
        }} >  
            <Spacer padding="0">
                <Spacer marginBottom="4" />
                { props.children }
            </Spacer>
        </div>  
    )
}

export {InfoPanel}
export default InfoPanel